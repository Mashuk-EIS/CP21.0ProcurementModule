<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\ORM\TableRegistry;

/**
 * Pr Controller
 *
 *
 * @method \App\Model\Entity\Pr[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class PrController extends AppController
{

    public function initialize(){
        parent::initialize();
        $this->viewBuilder()->setLayout('mainframe');
        set_time_limit(0);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function indexAuto()
    {
        $this->loadModel('PrAuto');
        $pr = $this->PrAuto->find('all')
        ->Where(['status'=>'requested']);

        $this->set(compact('pr'));
    }

    /**
     * View method
     *
     * @param string|null $id Pr id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function viewAuto($id = null)
    {
        $this->loadModel('PrAuto');
        $this->loadModel('PrAutoItems');
        $pr = $this->PrAuto->find('all')
            ->Where(['id'=> $id]);
        foreach ($pr as $p){
            $pri = $this->PrAutoItems->find('all')
                ->Where(['pr_auto_id'=>$p->id]);
            $p->pri = $pri;
        }

        $this->set('pr', $pr);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function addAuto()
    {
        $this->loadModel('Supplier');
        $this->loadModel('SupplierItems');
        $urlToSales = 'http://salesmodule.acumenits.com/api/all-data';

        $optionsForSales = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            ]
        ];
        $contextForSales  = stream_context_create($optionsForSales);
        $resultFromSales = file_get_contents($urlToSales, false, $contextForSales);
        if ($resultFromSales === FALSE) {
            echo 'ERROR!!';
        }
        $dataFromSales = json_decode($resultFromSales);

        $so_no = $customer = $model = $version = null;
        foreach ($dataFromSales as $d){
            $parts = '';
            foreach($d->soi as $item){
                $urlToEng = 'http://engmodule.acumenits.com/api/bom-parts';
                $sendToEng = [
                    'model' => $item->model,
                    'version' => $item->version
                ];
                $optionsForEng = [
                    'http' => [
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($sendToEng)
                    ]
                ];
                $contextForEng  = stream_context_create($optionsForEng);
                $resultFromEng = file_get_contents($urlToEng, false, $contextForEng);
                if ($resultFromEng !== FALSE) {
                    $dataFromEng = json_decode($resultFromEng);
                    $item->eng_data = $dataFromEng;
                    foreach($dataFromEng as $eng){
                        $uom = $supplier1 = $supplier2 = $supplier3 = '';
                        $price1 = $price2 = $price3 = 0;
                        $items = $this->SupplierItems->find('all', [
                            'order' => 'SupplierItems.unit_price'
                        ])
                            ->where(['part_no' => $eng->partNo])
                            ->where(['part_name' => $eng->partName]);
                        $count = 0;
                        foreach($items as $ii){
                            $supplier = $this->Supplier->get($ii->supplier_id, [
                                'contain' => []
                            ]);
                            $ii->supplier = $supplier;
                            $count++;
                            ${'supplier'.$count} = $supplier->name;
                            ${'price'.$count} = $ii->unit_price;
                            $uom = $ii->uom;
                        }
                        $stockAvailable = 0;
                        $urlToStore = 'http://storemodule.acumenits.com/in-stock-code/stock-available';
                        $sendToStore = [
                            'part_no' => $eng->partNo,
                            'part_name' => $eng->partName
                        ];


                        $optionsForStore = [
                            'http' => [
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'POST',
                                'content' => http_build_query($sendToStore)
                            ]
                        ];
                        $contextForStore = stream_context_create($optionsForStore);
                        $resultFromStore = file_get_contents($urlToStore, false, $contextForStore);
                        if($resultFromStore != FALSE){
                            $dataFromStore = json_decode($resultFromStore);
                            $stockAvailable = abs($dataFromStore->stock_available);
                        }
                        $parts .= '{
                            id:"'.$eng->id.'",
                            partNo:"'.$eng->partNo.'",
                            partName:"'.$eng->partName.'",
                            reqQuantity:"'.$eng->quality.'",
                            category:"'.$eng->category.'",
                            stockAvailable:"'.$stockAvailable.'",
                            supplier1:"'.$supplier1.'",
                            supplier2:"'.$supplier2.'",
                            supplier3:"'.$supplier3.'",
                            price1:"'.$price1.'",
                            price2:"'.$price2.'",
                            price3:"'.$price3.'",
                            uom:"'.$uom.'"
                            },';
                    }
                }
            }
            $parts = rtrim($parts, ',');
            $this->loadModel('PrAuto');
            $this->loadModel('PrAutoItems');
            $last_pr = $this->PrAuto->find('all')->last();
            foreach ($d->soi as $s){
                $model = $s->model;
                $version = $s->version;
            }
            foreach($d->cus as $cus){
                $customer = $cus->name;
            }
            $so_no .= '{label:"'.$d->salesorder_no.'",del_date:"'.date('Y-m-d', strtotime($d->delivery_date)).'",cus_name:"'.$customer.'",model:"'.$model.'",version:"'.$version.'",parts:['.$parts.']},';
        }
        $so_no = rtrim($so_no, ',');
        $this->loadModel('PrAuto');
        $this->loadModel('PrAutoItems');
        $last_pr = $this->PrAuto->find('all',['order'=>'id']);

        $this->set('so_no',$so_no);
        $this->set('pr_id', (isset($last_pr->id) ? ($last_pr->id + 1) : 1));
    }
    public function generateAuto(){
        $so_no = $this->request->getData('so_no');
        $date = $this->request->getData('date');
        $delivery_date = $this->request->getData('delivery_date');
        $description = $this->request->getData('description');
        $customer = $this->request->getData('customer');
        $pr_id = $this->request->getData('pr_id');
        $pr_items = array();
        if($this->request->getData('counter') != null) {
            for ($i = 1; $i <= $this->request->getData('counter'); $i++) {
                if($this->request->getData('checkbox'.$i)){
                    $pr_items[$i]['bom_part_id'] = $this->request->getData('bom_part_id'.$i);
                    $pr_items[$i]['part_no'] = $this->request->getData('part_no' . $i);
                    $pr_items[$i]['part_name'] = $this->request->getData('part_name' . $i);
                    $pr_items[$i]['supplier1'] = $this->request->getData('supplier1' . $i);
                    $pr_items[$i]['supplier2'] = $this->request->getData('supplier2' . $i);
                    $pr_items[$i]['supplier3'] = $this->request->getData('supplier3' . $i);
                    $pr_items[$i]['price1'] = $this->request->getData('price1' . $i);
                    $pr_items[$i]['price2'] = $this->request->getData('price2' . $i);
                    $pr_items[$i]['price3'] = $this->request->getData('price3' . $i);
                    $pr_items[$i]['uom'] = $this->request->getData('uom' . $i);
                    $pr_items[$i]['category'] = $this->request->getData('category' . $i);
                    $pr_items[$i]['req_quantity'] = $this->request->getData('reqQuantity' . $i);
                    $pr_items[$i]['stock_available'] = $this->request->getData('stockAvailable' . $i);
                    $pr_items[$i]['order_qty'] = $this->request->getData('order_qty' . $i);
                    $pr_items[$i]['supplier'] = $this->request->getData('supplier' . $i);
                    $pr_items[$i]['sub_total'] = $this->request->getData('sub_total' . $i);
                    $pr_items[$i]['gst'] = $this->request->getData('gst' . $i);
                    $pr_items[$i]['total'] = $this->request->getData('total' . $i);
                }
            }
        }
        $this->set('pr_items',$pr_items);
        $this->set('so_no',$so_no);
        $this->set('date',$date);
        $this->set('del_date',$delivery_date);
        $this->set('desc',$description);
        $this->set('pr_id',$pr_id);
        $this->set('cus',$customer);
    }
    public function submitAuto(){
        if($this->request->is('post')){
            $this->loadModel('PrAuto');
            $this->loadModel('PrAutoItems');
            $pr = $this->PrAuto->newEntity();
            $pr->date = $this->request->getData('date');
            $pr->so_no = $this->request->getData('so_no');
            $pr->delivery_date = $this->request->getData('delivery_date');
            $pr->description = $this->request->getData('description');
            $pr->customer = $this->request->getData('customer');
            $pr->status = 'requested';
            $pr_itm = array();
            $prChild = TableRegistry::get('prAutoItems');
            if($this->PrAuto->save($pr)){
                $pr_id = $this->PrAuto->find('all',['fields'=>'id'])->last();
                if($this->request->getData('total') != null){
                    for ($i=1;$i <= $this->request->getData('total');$i++){
                        $pr_itm[$i]['pr_auto_id'] = $pr_id['id'];
                        $pr_itm[$i]['bom_part_id'] = $this->request->getData('bom_part_id'.$i);
                        $pr_itm[$i]['order_qty'] = $this->request->getData('order_qty'.$i);
                        $pr_itm[$i]['order_qty'] = $this->request->getData('supplier' . $i);
                        $pr_itm[$i]['sub_total'] = $this->request->getData('sub_total' . $i);
                        $pr_itm[$i]['gst'] = $this->request->getData('gst' . $i);
                        $pr_itm[$i]['total'] = $this->request->getData('total' . $i);
                    }
                    $prs = $prChild->newEntities($pr_itm);
                    foreach ($prs as $p){
                        $prChild->save($p);
                    }
                }
                $this->Flash->success(__('The pr has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The pr could not be saved. Please, try again.'));
            }
        }

    public function addManual()
    {
        $this->loadModel('Supplier');
        $this->loadModel('SupplierItems');
        $urlToSales = 'http://salesmodule.acumenits.com/api/all-data';

        $optionsForSales = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            ]
        ];
        $contextForSales  = stream_context_create($optionsForSales);
        $resultFromSales = file_get_contents($urlToSales, false, $contextForSales);
        if ($resultFromSales === FALSE) {
            echo 'ERROR!!';
        }
        $dataFromSales = json_decode($resultFromSales);
        $so_no = null;
        foreach($dataFromSales as $pm){
            $parts = '';
            foreach($pm->soi as $item){
                $urlToEng = 'http://engmodule.acumenits.com/api/bom-parts';
                $sendToEng = [
                    'model' => $item->model,
                    'version' => $item->version
                ];


                $optionsForEng = [
                    'http' => [
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($sendToEng)
                    ]
                ];
                $contextForEng  = stream_context_create($optionsForEng);
                $resultFromEng = file_get_contents($urlToEng, false, $contextForEng);
                if ($resultFromEng !== FALSE) {
                    $dataFromEng = json_decode($resultFromEng);
                    $item->eng_data = $dataFromEng;
                    foreach($dataFromEng as $eng){
                        $supplierId1 = $supplierId2 = $supplierId3 = '';
                        $uom = $supplier1 = $supplier2 = $supplier3 = '';
                        $price1 = $price2 = $price3 = 0;
                        $items = $this->SupplierItems->find('all', [
                            'order' => 'SupplierItems.unit_price'
                        ])
                            ->where(['part_no' => $eng->partNo])
                            ->where(['part_name' => $eng->partName]);
                        $count = 0;
                        foreach($items as $ii){
                            $supplier = $this->Supplier->get($ii->supplier_id, [
                                'contain' => []
                            ]);
                            $ii->supplier = $supplier;
                            $count++;
                            if($count < 4){
                                ${'supplierId'.$count} = $supplier->id;
                                ${'supplier'.$count} = $supplier->name;
                                ${'price'.$count} = $ii->unit_price;
                            }
                            $uom = $ii->uom;
                        }
                        $stockAvailable = 0;
                        $urlToStore = 'http://storemodule.acumenits.com/in-stock-code/stock-available';
                        $sendToStore = [
                            'part_no' => $eng->partNo,
                            'part_name' => $eng->partName
                        ];


                        $optionsForStore = [
                            'http' => [
                                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method'  => 'POST',
                                'content' => http_build_query($sendToStore)
                            ]
                        ];
                        $contextForStore = stream_context_create($optionsForStore);
                        $resultFromStore = file_get_contents($urlToStore, false, $contextForStore);
                        if($resultFromStore !== FALSE){
                            $dataFromStore = json_decode($resultFromStore);
                            $stockAvailable = abs($dataFromStore->stock_available);
                        }
                        $parts .= '{partNo:"'.$eng->partNo.
                        '",bomId:"'.$eng->id.
                        '",partName:"'.$eng->partName.
                        '",reqQuantity:"'.$eng->quality.
                        '",category:"'.$eng->category.
                        '",stockAvailable:"'.$stockAvailable.
                        '",supplier1id:"'.$supplierId1.
                        '",supplier2id:"'.$supplierId2.
                        '",supplier3id:"'.$supplierId3.
                        '",supplier1:"'.$supplier1.
                        '",supplier2:"'.$supplier2.
                        '",supplier3:"'.$supplier3.
                        '",price1:"'.$price1.
                        '",price2:"'.$price2.
                        '",price3:"'.$price3.
                        '",uom:"'.$uom.'"},';
                    }
                }
            }
            $parts = rtrim($parts, ',');
            $customer = '';
            foreach($pm->cus as $cus){
                $customer = $cus->name;
            }
            $so_no .= '{label:"'.$pm->salesorder_no.'",del_date:"'.date('Y-m-d', strtotime($pm->delivery_date)).'",cus_name:"'.$customer.'",parts:['.$parts.']},';
        }
        $so_no = rtrim($so_no, ',');

        $part_nos = $part_names = '';
        $urlToEngBom = 'http://engmodule.acumenits.com/api/all-bom-parts';


        $optionsForEngBom = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'GET'
            ]
        ];
        $contextForEngBom  = stream_context_create($optionsForEngBom);
        $resultFromEngBom = file_get_contents($urlToEngBom, false, $contextForEngBom);
        if ($resultFromEngBom !== FALSE) {
            $dataFromEngBom = json_decode($resultFromEngBom);
            foreach($dataFromEngBom as $engBom){
                $bomSupplierId1 = $bomSupplierId2 = $bomSupplierId3 = '';
                $bomUom = $bomSupplier1 = $bomSupplier2 = $bomSupplier3 = '';
                $bomPrice1 = $bomPrice2 = $bomPrice3 = 0;
                $bomItems = $this->SupplierItems->find('all', [
                    'order' => 'SupplierItems.unit_price'
                ])
                    ->where(['part_no' => $engBom->partNo])
                    ->where(['part_name' => $engBom->partName]);
                $countBom = 0;
                foreach($bomItems as $bi){
                    $bomSupplier = $this->Supplier->get($bi->supplier_id, [
                        'contain' => []
                    ]);
                    $countBom++;
                    if($countBom < 4){
                        ${'bomSupplierId'.$countBom} = $bomSupplier->id;
                        ${'bomSupplier'.$countBom} = $bomSupplier->name;
                        ${'bomPrice'.$countBom} = $bi->unit_price;
                    }
                    $bomUom = $bi->uom;
                }
                $bomStockAvailable = 0;
                $urlToStoreBom = 'http://storemodule.acumenits.com/in-stock-code/stock-available';
                $sendToStoreBom = [
                    'part_no' => $engBom->partNo,
                    'part_name' => $engBom->partName
                ];


                $optionsForStoreBom = [
                    'http' => [
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($sendToStoreBom)
                    ]
                ];
                $contextForStoreBom = stream_context_create($optionsForStoreBom);
                $resultFromStoreBom = file_get_contents($urlToStoreBom, false, $contextForStoreBom);
                if($resultFromStoreBom !== FALSE){
                    $dataFromStoreBom = json_decode($resultFromStoreBom);
                    $bomStockAvailable = abs($dataFromStoreBom->stock_available);
                }
                $part_nos .= '{label:"'.$engBom->partNo.
                    '",bomId:"'.$engBom->id.
                    '",partName:"'.$engBom->partName.
                    '",reqQuantity:"'.$engBom->quality.
                    '",category:"'.$engBom->category.
                    '",stockAvailable:"'.$bomStockAvailable.
                    '",supplier1id:"'.$bomSupplierId1.
                    '",supplier2id:"'.$bomSupplierId2.
                    '",supplier3id:"'.$bomSupplierId3.
                    '",supplier1:"'.$bomSupplier1.
                    '",supplier2:"'.$bomSupplier2.
                    '",supplier3:"'.$bomSupplier3.
                    '",price1:"'.$bomPrice1.
                    '",price2:"'.$bomPrice2.
                    '",price3:"'.$bomPrice3.
                    '",uom:"'.$bomUom.'"},';
                $part_names .= '{label:"'.$engBom->partName.
                    '",bomId:"'.$engBom->id.
                    '",partNo:"'.$engBom->partNo.
                    '",reqQuantity:"'.$engBom->quality.
                    '",category:"'.$engBom->category.
                    '",stockAvailable:"'.$bomStockAvailable.
                    '",supplier1id:"'.$bomSupplierId1.
                    '",supplier2id:"'.$bomSupplierId2.
                    '",supplier3id:"'.$bomSupplierId3.
                    '",supplier1:"'.$bomSupplier1.
                    '",supplier2:"'.$bomSupplier2.
                    '",supplier3:"'.$bomSupplier3.
                    '",price1:"'.$bomPrice1.
                    '",price2:"'.$bomPrice2.
                    '",price3:"'.$bomPrice3.
                    '",uom:"'.$bomUom.'"},';
            }
        }
        $this->loadModel('PrManual');
        $this->loadModel('PrManualItems');
        $last_pr = $this->PrManual->find('all')->last();
        $pr = $this->PrManual->newEntity();
        if ($this->request->is('post')) {
            $pr = $this->PrManual->patchEntity($pr, $this->request->getData());
            if ($this->PrManual->save($pr)) {
                $this->Flash->success(__('The pr has been saved.'));

                return $this->redirect(['action' => 'indexAuto']);
            }
            $this->Flash->error(__('The pr could not be saved. Please, try again.'));
        }
        $part_nos = rtrim($part_nos, ',');
        $part_names = rtrim($part_names, ',');
        $this->set(compact('pr'));
        $this->set('last_pr', (isset($last_pr->id) ? ($last_pr->id + 1) : 1));
        $this->set('so_no', $so_no);
        $this->set('part_nos', $part_nos);
        $this->set('part_names', $part_names);
    }

    public function generateManual(){
        $this->loadModel('PrManual');
        $last_pr = $this->PrManual->find('all')->last();
        $allData = [];
        $showData = null;
        if($this->request->is('post')){
            $allData['date'] = $this->request->getData('date');
            $allData['so_no'] = $this->request->getData('so_no');
            $allData['del_date'] = $this->request->getData('del-date');
            $allData['cus_name'] = $this->request->getData('cus-name');
            $allData['purchase_type'] = $this->request->getData('purchase_type');
            $total_items = $this->request->getData('total-items');
            for($i = 1; $i <= $total_items; $i++){
                $allData['parts'][$i]['bom_id'] = $this->request->getData('bom-id-'.$i);
                $allData['parts'][$i]['part_no'] = $this->request->getData('part-no-'.$i);
                $allData['parts'][$i]['part_name'] = $this->request->getData('part-name-'.$i);
                if($this->request->getData('supplier'.$i) == 2){
                    $allData['parts'][$i]['supplier_id'] = $this->request->getData('supplier-2-'.$i);
                    $allData['parts'][$i]['price'] = $this->request->getData('price-2-'.$i);
                }elseif($this->request->getData('supplier'.$i) == 3){
                    $allData['parts'][$i]['supplier_id'] = $this->request->getData('supplier-3-'.$i);
                    $allData['parts'][$i]['price'] = $this->request->getData('price-3-'.$i);
                }else{
                    $allData['parts'][$i]['supplier_id'] = $this->request->getData('supplier-1-'.$i);
                    $allData['parts'][$i]['price'] = $this->request->getData('price-1-'.$i);
                }
                $allData['parts'][$i]['uom'] = $this->request->getData('uom-'.$i);
                $allData['parts'][$i]['category'] = $this->request->getData('category-'.$i);
                $allData['parts'][$i]['req_quantity'] = $this->request->getData('req-quantity-'.$i);
                $allData['parts'][$i]['stock'] = $this->request->getData('stock-'.$i);
                $allData['parts'][$i]['qty_order'] = $this->request->getData('qty_order'.$i);
                $allData['parts'][$i]['subtotal'] = $this->request->getData('subtotal'.$i);
                $allData['parts'][$i]['gst'] = $this->request->getData('gst'.$i);
                $allData['parts'][$i]['total'] = $this->request->getData('total'.$i);
            }
            $showData = (object) $allData;
        }
        $this->set('allData', $showData);
        $this->set('last_pr', (isset($last_pr->id) ? ($last_pr->id + 1) : 1));
    }

    public function submitManual(){
        $this->autoRender = false;
        $this->loadModel('PrManual');
        $this->loadModel('PrManualItems');
        $pr = $this->PrManual->newEntity();
        if ($this->request->is('post')) {
            $pr = $this->PrManual->patchEntity($pr, $this->request->getData());
            if ($this->PrManual->save($pr)) {
                $pr_no = $this->PrManual->find('all', ['fields' => 'id'])->last();
                if($this->request->getData('count') != null){
                    $prItems = TableRegistry::get('PrManualItems');
                    $items = array();
                    for($i = 1; $i <= $this->request->getData('count'); $i++){
                        $items[$i]['pr_manual_id'] = $pr_no['id'];
                        $items[$i]['bom_part_id'] = $this->request->getData('bom-id'.$i);
                        $items[$i]['supplier'] = $this->request->getData('supplier'.$i);
                        $items[$i]['order_qty'] = $this->request->getData('order_qty'.$i);
                        $items[$i]['sub_total'] = $this->request->getData('subtotal'.$i);
                        $items[$i]['gst'] = $this->request->getData('gst'.$i);
                        $items[$i]['total'] = $this->request->getData('total'.$i);
                    }
                    $allItems = $prItems->newEntities($items);
                    foreach($allItems as $item){
                        $prItems->save($item);
                    }
                }
                $this->Flash->success(__('The pr has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The pr could not be saved. Please, try again.'));
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Pr id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $pr = $this->Pr->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $pr = $this->Pr->patchEntity($pr, $this->request->getData());
            if ($this->Pr->save($pr)) {
                $this->Flash->success(__('The pr has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The pr could not be saved. Please, try again.'));
        }
        $this->set(compact('pr'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Pr id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $pr = $this->Pr->get($id);
        if ($this->Pr->delete($pr)) {
            $this->Flash->success(__('The pr has been deleted.'));
        } else {
            $this->Flash->error(__('The pr could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
