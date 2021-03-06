<!--=========
      Create serial number form page
      ==============-->

<div class="planner-from">
    <div class="container-fluid">
        <form action="<?php echo $this->Url->build(['controller'=>'Pr','action'=>'generateAuto'])?>" method="post" class="planner-relative">
        <div class="row">
            <div class="col-sm-12 col-sm-12">
                <div class="part-title-planner text-uppercase text-center"><b>PR 1 Generate (auto)</b></div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <label for="pr-date" class="planner-year">Date <span class="planner-fright">:</span></label>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <input name="date" type="text" class="form-control datepicker" id="pr-date" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">SO NO <span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <input name="so_no" type="text" class="form-control" name="so_no" id="so-no">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Delivery  Date <span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text" id="del-date"></p>
                                <input id="delivery-date" type="hidden" name="delivery_date" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Description<span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text text-uppercase" id="mod-ver"></p>
                                <input id="description" type="hidden" name="description" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Customer Name<span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text" id="cus-name"></p>
                                <input id="customer" type="hidden" name="customer" value="">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">PR NO <span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text">PR <?= $pr_id?></p>
                                <input id="pr-id" type="hidden" name="pr_id" value="PR <?= $pr_id?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Create by <span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p id="created-by" class="normal-text"><?= $user_pic ?></p>
                                <input id="created-name" type="hidden" name="created_by" value="<?= $user_id ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Department <span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text">Procurement</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Section<span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Verify<span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3 col-xs-6">
                                <p class="planner-year">Approve<span class="planner-fright">:</span></p>
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <p class="normal-text"></p>
                            </div>
                        </div>
                        <input type="hidden" name="check" id="check" value="">
                    </div>
            </div>
        </div>

        <div class="clearfix"></div>
        <!--============== Add drawing table area ===================-->
        <div class="planner-table table-responsive clearfix">
            <div class="col-sm-12">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>No</th>
                        <th>Select</th>
                        <th>Part No</th>
                        <th>Description</th>
                        <th>Supplier 1</th>
                        <th>Price (RM)</th>
                        <th>Supplier 2</th>
                        <th>Price (RM)</th>
                        <th>Supplier 3</th>
                        <th>UOM</th>
                        <th>Price (RM)</th>
                        <th>Category</th>
                        <th>QTY Request</th>
                        <th>Stock Available</th>
                        <th>QTY Order</th>
                        <th>Select Supplier</th>
                        <th>Sub Total</th>
                        <th>GST%</th>
                        <th>GST amount</th>
                        <th>Total</th>
                        <th>Document</th>
                        <th>Remark</th>
                    </tr>
                    </thead>
                    <tbody class="csn-text-up" id="parts-data">
                    </tbody>
                </table>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="col-sm-offset-8 col-sm-4 col-xs-12">
            <div class="prepareted-by-csn">
                <button id="generate-auto" type="submit" class="button btn btn-info">Generate PR</button>
            </div>
        </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        var counter = 0;
        var so_no = 'input#so-no';
        var data = [<?php echo $so_no; ?>];
        var options = {
            source: data,
            minLength: 0
        };
        $(document).on('keydown.autocomplete', so_no, function () {
            $(this).autocomplete(options);
        });
        $(document).on('autocompleteselect', so_no, function (e, ui) {
            $('#parts-data').empty();
            $('#del-date').text(ui.item.del_date);
            $('#delivery-date').val(ui.item.del_date);
            $('#cus-name').text(ui.item.cus_name);
            $('#customer').val(ui.item.cus_name);
            $('#mod-ver').text(ui.item.model + ' (' + ui.item.version + ') ');
            $('#description').val(ui.item.model + ' (' + ui.item.version + ') ');
            var parts = ui.item.parts;
            var html_table = '';
            if(parts.length !== 0){
                $.each(parts, function(i, e){
                    counter++;
                    var order_qty = e.reqQuantity - e.stockAvailable;
                    if(order_qty < 0){
                        order_qty = 0;
                    }
                    html_table += '<tr>'+
                        '<td>'+counter+'</td>'+
                        '<td><input id="bom-id" type="hidden" name="bom_part_id'+counter+'" value="'+e.bomId+'"><input name="checkbox'+counter+'" type="checkbox" class="form-check-input" id="check" value="'+counter+'"></td>'+
                        '<td>'+ e.partNo+'<input type="hidden" name="part_no'+counter+'" value="'+ e.partNo+'"></td>'+
                        '<td>'+ e.partName+'<input type="hidden" name="part_name'+counter+'" value="'+ e.partName+'"></td>'+
                        '<td>'+ e.supplier1+'<input type="hidden" name="supplier-1-'+counter+'" value="'+ e.supplier1id+'"><input type="hidden" name="sup-item-1-'+counter+'" value="'+ e.supItemId1+'"></td>'+
                        '<td>$ <p class="text-right" id="price-1'+counter+'">'+ e.price1+'</p><input type="hidden" name="price-1-'+counter+'" value="'+ e.price1+'"></td>'+
                        '<td>'+ e.supplier2+'<input type="hidden" name="supplier-2-'+counter+'" value="'+ e.supplier2id+'"><input type="hidden" name="sup-item-2-'+counter+'" value="'+ e.supItemId2+'"></td>'+
                        '<td>$ <p class="text-right" id="price-2'+counter+'">'+ e.price2+'</p><input type="hidden" name="price-2-'+counter+'" value="'+ e.price2+'"></td>'+
                        '<td>'+ e.supplier3+'<input type="hidden" name="supplier-3-'+counter+'" value="'+ e.supplier3id+'"><input type="hidden" name="sup-item-3-'+counter+'" value="'+ e.supItemId1+'"></td>'+
                        '<td>'+ e.uom+'<input type="hidden" name="uom'+counter+'" value="'+ e.uom+'"></td>'+
                        '<td>$ <p class="text-right" id="price-3'+counter+'">'+ e.price3+'</p><input type="hidden" name="price-3-'+counter+'" value="'+ e.price3+'"></td>'+
                        '<td>'+ e.category+'<input type="hidden" name="category'+counter+'" value="'+ e.category+'"></td>'+
                        '<td>'+ e.reqQuantity+'<input type="hidden" name="reqQuantity'+counter+'" value="'+ e.reqQuantity+'"></td>'+
                        '<td>'+ e.stockAvailable+'<input type="hidden" name="stockAvailable'+counter+'" value="'+ e.stockAvailable+'"></td>'+
                        '<td><input type="number" class="form-control qty-order" id="qty'+counter+'" rel="'+counter+'" name="order_qty'+counter+'" value="'+order_qty+'"></td>'+
                        '<td><select class="form-control all-supp" id="supp'+counter+'" rel="'+counter+'" name="supplier'+counter+'"><option value="1">Supplier 1</option><option value="2">Supplier 2</option><option value="3">Supplier 3</option></select></td>'+
                        '<td><p id="sub-total-text'+counter+'">'+(order_qty * e.price1)+'</p><input type="hidden" name="sub_total'+counter+'" id="subtotal'+counter+'" value="'+(order_qty * e.price1)+'"></td>'+
                        '<td><input type="number" class="form-control gst" id="gst'+counter+'" rel="'+counter+'" name="gst'+counter+'" value="6"></td>'+
                        '<td><p id="gst-amount'+counter+'">'+((order_qty * e.price1) * 6)/100 +'</p></td>'+
                        '<td><p id="total-text'+counter+'">'+(((order_qty * e.price1) * 6)/100 + (order_qty * e.price1))+'</p><input type="hidden" name="total'+counter+'" id="total'+counter+'" value="'+(((order_qty * e.price1) * 6)/100 + (order_qty * e.price1))+'"></td>'+
                        '<td><a href="#">View</a></td>'+
                        '<td></td>'+
                        '<input type="hidden" name="counter" value="'+counter+'">'+
                        '</tr>';
                });
            }
            if($('#append-here').length == 0){
                html_table += '<tr id="append-here">'+
                    '<td colspan="19"></td>'+
                    '<td><p id="final-total"></p></td>'+
                    '<td colspan="2"></td>'+
                    '</tr>';
                $('#parts-data').append(html_table);
                var finalTotal = 0;
                for(k = 1; k <= counter; k++){
                    finalTotal += parseInt($('#total-text'+k).text());
                }
                $('#final-total').text(finalTotal);
            }else{
                $('#append-here').before(html_table);
                var finalTotal = 0;
                for(k = 1; k <= counter; k++){
                    finalTotal += parseInt($('#total-text'+k).text());
                }
                $('#final-total').text(finalTotal);
            }
            $('.all-supp').on('change', function(e){
                e.preventDefault();
                var relate = $(this).attr('rel');
                var price = 0;
                var selectedSup = $('#supp'+relate+' :selected').val();
                if(selectedSup === '2'){
                    price = $('#price-2'+relate).text();
                }else if(selectedSup === '3'){
                    price = $('#price-3'+relate).text();
                }else{
                    price = $('#price-1'+relate).text();
                }
                var qty_order = $('#qty'+relate).val();
                var gst = $('#gst'+relate).val();
                $('#subtotal'+relate).val(price*qty_order);
                $('#total'+relate).val((price*qty_order)+(((price*qty_order)*gst)/100));
                $('#sub-total-text'+relate).text(price*qty_order);
                $('#gst-amount'+relate).text(((price*qty_order)*gst)/100);
                $('#total-text'+relate).text((price*qty_order)+(((price*qty_order)*gst)/100));
                var finalTotal = 0;
                for(k = 1; k <= counter; k++){
                    finalTotal += parseInt($('#total-text'+k).text());
                }
                $('#final-total').text(finalTotal);
            });
            $('.qty-order').on('change', function(e){
                e.preventDefault();
                var relate = $(this).attr('rel');
                var price = 0;
                var selectedSup = $('#supp'+relate+' :selected').val();
                if(selectedSup === '2'){
                    price = $('#price-2'+relate).text();
                }else if(selectedSup === '3'){
                    price = $('#price-3'+relate).text();
                }else{
                    price = $('#price-1'+relate).text();
                }
                var qty_order = $('#qty'+relate).val();
                var gst = $('#gst'+relate).val();
                $('#subtotal'+relate).val(price*qty_order);
                $('#total'+relate).val((price*qty_order)+(((price*qty_order)*gst)/100));
                $('#sub-total-text'+relate).text(price*qty_order);
                $('#gst-amount'+relate).text(((price*qty_order)*gst)/100);
                $('#total-text'+relate).text((price*qty_order)+(((price*qty_order)*gst)/100));
                var finalTotal = 0;
                for(k = 1; k <= counter; k++){
                    finalTotal += parseInt($('#total-text'+k).text());
                }
                $('#final-total').text(finalTotal);
            });
            $('.gst').on('change', function(e){
                e.preventDefault();
                var relate = $(this).attr('rel');
                var price = 0;
                var selectedSup = $('#supp'+relate+' :selected').val();
                if(selectedSup === '2'){
                    price = $('#price-2'+relate).text();
                }else if(selectedSup === '3'){
                    price = $('#price-3'+relate).text();
                }else{
                    price = $('#price-1'+relate).text();
                }
                var qty_order = $('#qty'+relate).val();
                var gst = $('#gst'+relate).val();
                $('#subtotal'+relate).val(price*qty_order);
                $('#total'+relate).val((price*qty_order)+(((price*qty_order)*gst)/100));
                $('#sub-total-text'+relate).text(price*qty_order);
                $('#gst-amount'+relate).text(((price*qty_order)*gst)/100);
                $('#total-text'+relate).text((price*qty_order)+(((price*qty_order)*gst)/100));
                var finalTotal = 0;
                for(k = 1; k <= counter; k++){
                    finalTotal += parseInt($('#total-text'+k).text());
                }
                $('#final-total').text(finalTotal);
            });
        });
    });
</script>