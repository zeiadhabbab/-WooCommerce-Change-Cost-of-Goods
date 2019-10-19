<?php
/**
 * Plugin Name: WooCommerce Change Cost of Goods
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-change-cost-of-goods/
 * Description: 
 * Author: A2Z Advanced Solutions 
 * Author URI: http://www.woocommerce.com
 * Version: 1.0.1
 * Text Domain: woo-change-cost-of-goods
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2013-2018, SkyVerge, Inc. (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 2.6.14
 * WC tested up to: 3.5.0
 */
 
defined( 'ABSPATH' ) or exit;
 

function change_cost_of_goods_callback() {
    
	$screen = get_current_screen();
		if( 'add' != $screen->action ){
		add_meta_box(
			'change-cost-of-goods',
			__( 'Add To Inventory', 'woo-change-cost-of-goods' ),
			'change_cost_of_goods_html_callback',
			'product'
		);
	}
}

function change_cost_of_goods_html_callback(  $post) {
echo '

		<style>
		.cc_div_r lable{
				display: inline-block;
		}
		.cc_div_r{
			    margin-bottom: 10px;
				min-height: 75px;
		}
		.cc_div_r.first{
			    display: block;
				float:left;
				width:45%;
		}
		.cc_div_r.last{
			    display: block;
				float:right;
				width:45%;
		}
		
		.cc_div_r.wid lable{
			    width:200px
				
		}
		</style>
		';
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'global_notice_nonce', 'global_notice_nonce' );

	
	echo '<div>
			<p>
			Make sure that "Enable stock management at product level" option enabled for all products
			</p>
		</div>';
	
	//$product = new WC_Product($post->ID);
	$product  = new WC_Product_Variable($post->ID);
	//print_r(  $product ) ;//$product->has_child();
	if( count($product->get_children())> 0){
		
		
		foreach($product->get_children() as $item){
			
			echo '
			<div class="cc_div_r first">
				<input type="hidden" name="ctype" value="variable">
				<lable for="new_qty">'.__('Add Stock Quantity For ','woo-change-cost-of-goods') . get_the_title($item).'</lable>	
				<input type="number"  step=0.01 name="cust_cost[new_qty_'.$item.']">
			</div>
			<div class="cc_div_r last">
				<lable for="new_price">' .__('Add Item Price','woo-change-cost-of-goods'). get_the_title($item) .'</lable>
				<input type="number"  step=0.01 name="cust_cost[new_price_'.$item.']">
			</div>
			
			';
			
			
		}
		
		echo '
			<div style="border-bottom:1px solid #ddd; clear:both; margin-bottom:20px"></div>
			<div><strong>Packs:</strong> '.get_post_meta( $post->ID, 'c_units', true ).'</div>
			<div><strong>Coils:</strong> '.get_post_meta( $post->ID, 'cs_units', true ).'</div>
			<div style="border-bottom:1px solid #ddd; clear:both; margin-bottom:20px; margin-top:20px"></div>
			<div class="cc_div_r">
				<lable for="c_units" style="width: 70px;display: inline-block;">' .__('Packs:','woo-change-cost-of-goods').'</lable>
				<input type="text" name="c_units">
			</div>
			<div class="cc_div_r">
				<lable for="cs_units" style="width: 70px;display: inline-block;">' .__('Coils:','woo-change-cost-of-goods').'</lable>
				<input type="text" name="cs_units">
			</div>
		';
		
		echo '
		<div style="padding: 10px;clear: both;border-top: 1px solid #ddd;background: #f5f5f5;    text-align: right;">
			<input name="save" type="submit" class="button button-primary button-large"  value="Add Stock">
		</div>
		';
		
	  
	}else{
		
		 echo '
		
		<input type="hidden" name="ctype" value="simple">
		
		<div class="cc_div_r wid">
			<lable for="new_qty">' .__('Add Stock Quantity','woo-change-cost-of-goods') .'</lable>
			<input type="number" step=0.01 name="new_qty">
		</div>
		<div class="cc_div_r wid">
			<lable for="new_price">' .__('Add Item Price','woo-change-cost-of-goods') .'</lable>
			<input type="number" step=0.01 name="new_price">
		</div>
		
		
		
		<input name="save" type="submit" class="button button-primary button-large"  value="Add Stock">
		';
	}
 	

   
	
	
	  
}

add_action( 'add_meta_boxes', 'change_cost_of_goods_callback' );


function save_global_notice_meta_box_data( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['global_notice_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['global_notice_nonce'], 'global_notice_nonce' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'product' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */
    // Make sure that it is set.
    if ( !isset( $_POST['ctype'] ) ) {
        return;
    }

	if($_POST['ctype'] == "simple"){
		if ( !isset( $_POST['new_qty'] ) ) {
			return;
		}
		if ( !isset( $_POST['new_price'] ) ) {
			return;
		}
		// Sanitize user input.
		$new_qty = sanitize_text_field( $_POST['new_qty'] );
		$new_cost = sanitize_text_field( $_POST['new_price'] );
		
		$old_qty = get_post_meta( $post_id, '_stock', true );
		$old_cost = get_post_meta( $post_id , '_wc_cog_cost', true );
		
		$final_qty = $old_qty + $new_qty;
		$final_cost = round(( ( $old_qty * $old_cost ) + ($new_qty * $new_cost) ) / $final_qty, 2); 

		// Update the meta field in the database.
		update_post_meta( $post_id, '_wc_cog_cost', $final_cost );
		update_post_meta( $post_id, '_stock', $final_qty );print_r($_POST);

	}else{
		try{
			// great, no exceptions where thrown while creating the object
			$product  = new WC_Product_Variable($post_id);
			$totalq = 0;
			if( count($product->get_children())> 0){
			
				foreach($product->get_children() as $item){
					 
					if ( !isset( $_POST['cust_cost']['new_qty_'.$item] ) ) {
						return;
					}
					if ( !isset( $_POST['cust_cost']['new_price_'.$item] ) ) {
						return;
					}
					if(isset( $_POST['cust_cost']['new_price_'.$item] ) && isset( $_POST['cust_cost']['new_qty_'.$item] )){
						// Sanitize user input.
						$new_qty = sanitize_text_field( $_POST['cust_cost']['new_qty_'.$item] );
						$new_cost = sanitize_text_field( $_POST['cust_cost']['new_price_'.$item] );
						
						$old_qty = get_post_meta( $item, '_stock', true );
						$old_cost = get_post_meta( $item , '_wc_cog_cost', true );
											
						$final_qty = $old_qty + $new_qty;
						$totalq = $totalq + $final_qty;
						$final_cost = round(( ( $old_qty * $old_cost ) + ($new_qty * $new_cost) ) / $final_qty, 2); 

						// Update the meta field in the database.
						update_post_meta( $item, '_wc_cog_cost', $final_cost );
						update_post_meta( $item, '_stock', $final_qty );
					}
					
				}
				if( isset($_POST['c_units']) && $_POST['c_units'] !=""){
					update_post_meta( $post_id, 'c_units', $_POST['c_units']);
				}
				if( isset($_POST['cs_units']) && $_POST['cs_units'] !=""){
					update_post_meta( $post_id, 'cs_units', $_POST['cs_units'] );
				}
				
				
				
				update_post_meta( $post_id, '_stock', $totalq );
			}
		} catch (\Exception $ex) {
			echo $ex->getMessage();
		}
		
		
		//print_r($product);
		//exit;
		
		
		
	}
}

add_action( 'save_post', 'save_global_notice_meta_box_data' );








/**
 * Register a custom menu page.
 */
function wpdocs_register_my_custom_menu_page(){
    add_menu_page( 
        __( 'What If', 'textdomain' ),
        'What If',
        'manage_options',
        'whatif',
        'what_if_custom_menu_page',
        plugins_url( 'woocommerce-change-cogs/icon1.png' ),
        6
    ); 
}
add_action( 'admin_menu', 'wpdocs_register_my_custom_menu_page' );
 
/**
 * Display a custom menu page
 */
function what_if_custom_menu_page(){
    ?>
	<style>
	input[type="text"]{
		    max-width: 70px !important;
	}
	
	table.dataTable.nowrap th{
		background-color: #fff;
	}
	
	#float {
        position: fixed;
        top: 3em;
        right: 2em;
        z-index: 100;
    }
	
	table.fixedHeader-floating.no-footer {
		border-bottom-width: 0;
		margin-top: 32px;
	}
	
	table.fixedHeader-floating.no-footer {
		border-bottom-width: 0;
		overflow: hidden;
		display: block;
		width: 88% !important;
	}

	</style>
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedheader/3.1.5/css/fixedHeader.dataTables.min.css">
  
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/3.2.1/js/dataTables.fixedColumns.min.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.5/js/dataTables.fixedHeader.min.js"></script>

	<script>
	
	
	jQuery(document).ready( function ($) {
		
		jQuery('#table_id').scroll(function() {
			if ( jQuery(".fixedHeader-floating").is(":visible") ) {
				jQuery(".fixedHeader-floating").scrollLeft( jQuery(this).scrollLeft() );
			}
		});


		var table = $('#table_id').DataTable({
				"ajax": ajaxurl + "?action=returndata",
				"deferRender": true,
				"scrollX": true,
				"scrollCollapse": true,
				"fixedHeader": true,
				"fixedColumns":   {
					leftColumns: 1
				},
				"columns": [
					{ "data": "post_title" },
					{ "data": "edit_link" },
					{ "data": "cs_units"},
					{ "data": "c_units" },
					{ "data": "stock",
						render: function (data, type, row) {
                                return '<input readonly id="stock_'+row.ID+'" data-id="'+row.ID+'" class="oinput form-control" type="text"  value=' + data + '>';
                        } },
					{ "data": "wc_cog_cost" },
					{ "data": "value",
					render: function (data, type, row) {
                                return '<input class="oinput form-control" id="old_val_'+row.ID+'" data-id="'+row.ID+'" type="text"  value =' + Math.ceil(row.stock * row.wc_cog_cost) + '>';
                        }},
					{ "data": "new_stock",
						render: function (data, type, row) {
                                return '<input class="oinput form-control" id="ns_'+row.ID+'" data-id="'+row.ID+'" type="text"  value =' + data + '>';
                        } 
					},
					{ "data": "new_cost",
						render: function (data, type, row) {
                                return '<input class="oinput form-control" id="nc_'+row.ID+'" data-id="'+row.ID+'" type="text"  value =' + data + '>';
                        } 
					},
					{ "data": "new_value",
						render: function (data, type, row) {
                                return '<input id="nv_'+row.ID+'" data-id="'+row.ID+'" readonly class="oinput form-control" type="text"  value =' + data + '>';
                        }					
					},
					{ "data": "total_inventory_stock",
						render: function (data, type, row) {
                                return '<input id="total_inventory_stock_'+row.ID+'" data-id="'+row.ID+'" readonly class="oinput form-control" type="text"  value =' + data + '>';
                        }					
					},
					
					
					{ "data": "Avg_COGS",
						render: function (data, type, row) {
                                return '<input id="Avg_COGS_'+row.ID+'" data-id="'+row.ID+'" readonly class="oinput form-control" type="text"  value =' + data + '>';
                        }					
					},
					{ "data": "Wholesale_Price",
						render: function (data, type, row) {
                                return '<input id="Wholesale_Price_'+row.ID+'" data-id="'+row.ID+'" class=" oinput form-control" type="text"  value =' + data + '>';
                        }					
					}
					,{ "data": "Wholesale_Profit",
						render: function (data, type, row) {
                                return '<input id="Wholesale_Profit_'+row.ID+'" data-id="'+row.ID+'" readonly class="oinput form-control" type="text"  value =' + data + '>';
                        }					
					}
					,{ "data": "Wholesale_Profit_Margin",
						render: function (data, type, row) {
                                return '<input id="Wholesale_Profit_Margin_'+row.ID+'" data-id="'+row.ID+'" readonly class="oinput form-control" type="text"  value =' + data + '>%';
                        }					
					},
					{ "data": "Retail_Price",
						render: function (data, type, row) {
                                return '<input id="Retail_Price_'+row.ID+'" data-id="'+row.ID+'" class="oinput form-control" type="text"  value =' + data + '>';
                        }					
					},
					{ "data": "Retail_Profit",
						render: function (data, type, row) {
                                return '<input id="Retail_Profit_'+row.ID+'" data-id="'+row.ID+'" readonly class="oinput form-control" type="text"  value =' + data + '>';
                        }					
					},
					{ "data": "Retail_Profit_Margin",
						render: function (data, type, row) {
                                return '<input id="Retail_Profit_Margin_'+row.ID+'" data-id="'+row.ID+'" readonly class="oinput form-control" type="text"  value =' + data + '>%';
                        }					
					},
				
					{ "data": "Save_Data",
						render: function (data, type, row) {
                                return '<button data-id="'+row.ID+'" class="savebtn">Save</button>';
                        }					
					},
					
					
				],
				"columnDefs": [
					{
						// The `data` parameter refers to the data for the cell (defined by the
						// `data` option, which defaults to the column being worked with, in
						// this case `data: 0`.
						"render": function ( data, type, row ) {
							return '<a href='+ row.edit_link +'>'+ data +'( #' +row.ID+ ' )' + '</a>';
						},
						"targets": 0
					},
					
					{ "visible": false,  "targets": [ 1,2,3 ] }
				]
				,"initComplete": function(settings, json){
							
						do_binding();
						jQuery('.dataTables_scrollBody').on('scroll',function(e){
							
							jQuery('.fixedHeader-floating').scrollLeft( jQuery(this).scrollLeft() );


							});
							
							
						
					}
			} );
			
			
			table.on( 'draw', function () {
				
				do_binding();
				
				
					jQuery('.oinput').each(function() {
						var d = jQuery('#stock_' + jQuery(this).data('id'));
						var f = jQuery('#old_val_' + jQuery(this).data('id'));
						var h = jQuery('#ns_' + jQuery(this).data('id'));
						var i = jQuery('#nc_' + jQuery(this).data('id'));
					    var j = jQuery('#nv_' + jQuery(this).data('id'));
						var l =  jQuery('#total_inventory_stock_' + jQuery(this).data('id'));
						var m =  jQuery('#Avg_COGS_' + jQuery(this).data('id'));
						var o =  jQuery('#Wholesale_Price_' + jQuery(this).data('id'));
						var p =  jQuery('#Wholesale_Profit_' + jQuery(this).data('id'));
						var q =  jQuery('#Wholesale_Profit_Margin_' + jQuery(this).data('id'));
						
						var s =  jQuery('#Retail_Price_' + jQuery(this).data('id'));
						var t =  jQuery('#Retail_Profit_' + jQuery(this).data('id'));
						var u =  jQuery('#Retail_Profit_Margin_' + jQuery(this).data('id'));
						
						j.val( (parseFloat(h.val())  *  parseFloat(i.val())).toFixed(2));
						l.val( (parseFloat(d.val())  +  parseFloat(h.val())).toFixed(2));
						m.val( ((parseFloat(j.val())  +  parseFloat(f.val())) / l.val()  ).toFixed(2));
					    p.val( (parseFloat(o.val())  -  parseFloat(m.val()) ).toFixed(2));
					    q.val( ((parseFloat(p.val())  /  parseFloat(o.val())) * 100).toFixed(2));
					    t.val( (parseFloat(s.val())  -  parseFloat(m.val()) ).toFixed(2));
					    u.val( ((parseFloat(t.val())  /  parseFloat(s.val())) * 100).toFixed(2));
					  
					   
					  
					
					});
					
			} );
	} );



	function do_binding(){
			jQuery(".oinput").unbind();
			jQuery(".savebtn").unbind();
					 
					jQuery('.oinput').change(function() {
						jQuery(this).css("background", "#ffadad");
						var d = jQuery('#stock_' + jQuery(this).data('id'));
						var f = jQuery('#old_val_' + jQuery(this).data('id'));
						var h = jQuery('#ns_' + jQuery(this).data('id'));
						var i = jQuery('#nc_' + jQuery(this).data('id'));
					    var j = jQuery('#nv_' + jQuery(this).data('id'));
						var l =  jQuery('#total_inventory_stock_' + jQuery(this).data('id'));
						var m =  jQuery('#Avg_COGS_' + jQuery(this).data('id'));
						var o =  jQuery('#Wholesale_Price_' + jQuery(this).data('id'));
						var p =  jQuery('#Wholesale_Profit_' + jQuery(this).data('id'));
						var q =  jQuery('#Wholesale_Profit_Margin_' + jQuery(this).data('id'));
						
						var s =  jQuery('#Retail_Price_' + jQuery(this).data('id'));
						var t =  jQuery('#Retail_Profit_' + jQuery(this).data('id'));
						var u =  jQuery('#Retail_Profit_Margin_' + jQuery(this).data('id'));
						
						j.val( (parseFloat(h.val())  *  parseFloat(i.val())).toFixed(2));
						l.val( (parseFloat(d.val())  +  parseFloat(h.val())).toFixed(2));
						m.val( ((parseFloat(j.val())  +  parseFloat(f.val())) / l.val()  ).toFixed(2));
					    p.val( (parseFloat(o.val())  -  parseFloat(m.val()) ).toFixed(2));
					    q.val( ((parseFloat(p.val())  /  parseFloat(o.val())) * 100).toFixed(2));
					    t.val( (parseFloat(s.val())  -  parseFloat(m.val()) ).toFixed(2));
					    u.val( ((parseFloat(t.val())  /  parseFloat(s.val())) * 100).toFixed(2));
					  
					   
					  
					
					});
					
					jQuery('.savebtn').click(function(){
						var id = jQuery(this).data('id');
						var avg_cogs = jQuery('#Avg_COGS_' + jQuery(this).data('id')).val();
						var Wholesale_Price = jQuery('#Wholesale_Price_' + jQuery(this).data('id')).val();
						var Wholesale_Price = jQuery('#Wholesale_Price_' + jQuery(this).data('id')).val();
						var Retail_Price = jQuery('#Retail_Price_' + jQuery(this).data('id')).val();
						
						
						var total_inventory_stock = jQuery('#total_inventory_stock_' + jQuery(this).data('id')).val();
						var new_stock_s = jQuery('#ns_' + jQuery(this).data('id')).val();
						
						if(avg_cogs > 0 && total_inventory_stock > 0){
							
							
							
							var data = {
								'action': 'savanewdata',
								'id': id,
								'avg_cogs': avg_cogs,
								'Retail_Price': Retail_Price,
								'Wholesale_Price': Wholesale_Price,
								'total_inventory_stock': total_inventory_stock,
								'new_stock_s': new_stock_s
							};
						
							var r = confirm("Are you sure you want to change to the new data!");
							
							if (r == true) {
								
								jQuery.post(ajaxurl, data, function(response) {
									console.log(response);
									location.reload();
								});
							}
							
						
						}else{
							alert("Insert New Values First");
						}
						
					});
					
					
					
	}
	
	</script>
	<h1 class="wp-heading-inline">What If</h1>
	
	<table id="table_id"  class="stripe display nowrap row-border order-column" style="width:100%">
		<thead>
            <tr>
                <th>Name</th>
                <th>Link</th>
                <th>Coils in Pack</th>
                <th>Packs in Box</th>
                <th>Current Stock</th>
                <th>Current Cost</th>
                <th>Value</th>
				<th>New Stock</th>
                <th>New Cost</th>
                <th>New Value</th>
                <th>Total Inventory Stock</th>
                <th>Avg COGS</th>
                <th>Wholesale Price</th>
                <th>Wholesale Profit</th>
                <th>Wholesale Profit Margin</th>
                <th>Retail Price</th>
                <th>Retail Profit</th>
                <th>Retail Profit Margin</th>
                <th>Save</th>
            </tr>
        </thead>

	</table>
	
	<?php
	
	
	
}

add_action( 'wp_ajax_returndata', 'returndata' );
add_action( 'wp_ajax_nopriv_returndata', 'returndata' );

	function returndata() {
		global $wpdb; // this is how you get access to the database
		header('Content-Type: application/json');

		 $args = array(
			'post_type'      => array('product','product_variation'),
			'posts_per_page' => 1000000,
		);

		$loop = new WP_Query( $args );
		$poructs = [];
		while ( $loop->have_posts() ) : $loop->the_post();
			global $product;
			 $tdata = get_post(get_the_ID(),ARRAY_A);
			if(get_post_meta($tdata['ID'],'_stock',true) != null){
					$ti = $tdata['post_title'];
					$ti = str_replace(' ','+',$ti);
				 $tdata['edit_link'] = 'https://vaporsplanet.com/wp-admin/edit.php?s='.$ti.'&post_status=all&post_type=product&action=-1&seo_filter&readability_filter&product_cat&product_type&stock_status&paged=1';
				 
				 
				 //$tdata['edit_link'] = get_edit_post_link($tdata['ID']);
				 $tdata['cs_units'] = get_post_meta($tdata['ID'],'cs_units', true);
				 $tdata['c_units'] = get_post_meta($tdata['ID'],'c_units',true);
				 $tdata['stock'] = get_post_meta($tdata['ID'],'_stock',true);
				 $tdata['wc_cog_cost'] = get_post_meta($tdata['ID'],'_wc_cog_cost',true);
				 $tdata['new_stock'] = 0;
				 $tdata['new_cost'] = 0;
				 $tdata['new_value'] = 0;
				 $tdata['total_inventory_stock'] = 0;
				 $tdata['Avg_COGS'] = 0;


				 
				$tdata['Wholesale_Price'] = get_post_meta($tdata['ID'],'wholesale_customer_wholesale_price',true);
				$tdata['Wholesale_Profit'] = 0;
				$tdata['Wholesale_Profit_Margin'] = 0;
				$tdata['Retail_Price'] =  get_post_meta($tdata['ID'],'_price',true);
				$tdata['Retail_Profit'] = 0;
				$tdata['Retail_Profit_Margin'] = 0;
				$tdata['Save_Data'] = $tdata['ID'];
				 
				 
				 $poructs[] = $tdata;
			}
			 
		endwhile;
		
		wp_reset_query(); 
		echo json_encode(array("data"=>$poructs));
		wp_die(); // this is required to terminate immediately and return a proper response
	}




add_action( 'wp_ajax_savanewdata', 'savanewdata' );
function savanewdata() {
	$id = $_POST['id'];
	$avg_cogs = $_POST['avg_cogs'];
	$total_inventory_stock = $_POST['total_inventory_stock'];
	$new_stock_s = $_POST['new_stock_s'];
	$_price = $_POST['Retail_Price'];
	$wholesale_customer_wholesale_price = $_POST['Wholesale_Price'];
	
	$product = wc_get_product($id);
	
	update_post_meta( $id, '_wc_cog_cost', $avg_cogs );
	update_post_meta( $id, '_stock', $total_inventory_stock );
	if($product->parent_id)
	{
		update_post_meta( $product->parent_id, '_stock', (int)( (int) $new_stock_s 
		+ (int)get_post_meta($product->parent_id,'_stock',true) ));

	}
	
	update_post_meta( $id, 'wholesale_customer_wholesale_price', $wholesale_customer_wholesale_price );
	update_post_meta( $id, '_regular_price', $_price );
	update_post_meta( $id, '_price', $_price );
	update_post_meta( $id, '_sale_price', $_price );
	print_r($product);
	echo  get_post_meta( $id,'_price',true);
	wp_die(); // this is required to terminate immediately and return a proper response
}










