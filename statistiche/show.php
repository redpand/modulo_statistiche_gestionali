<?php $userdata_logged = $this->frontauth->is_logged(); ?>
<script src="<?php echo base_url().PUBLIC_FRONT_DIR.JS_DIR; ?>/libs/highcharts/highcharts.js"></script>
<script src="<?php echo base_url().PUBLIC_FRONT_DIR.JS_DIR; ?>/libs/highcharts/modules/exporting.js"></script>
<script src="<?php echo base_url().PUBLIC_FRONT_DIR.JS_DIR; ?>statistiche_ajax.js" type="text/javascript"></script>
<script src="<?php echo base_url().PUBLIC_FRONT_DIR.JS_DIR; ?>/libs/jquery.iframe-post-form.min.js" type="text/javascript"></script>
<script src="<?php echo base_url().PUBLIC_FRONT_DIR.JS_DIR; ?>/allegati.js" type="text/javascript"></script>


<script type="text/javascript">
    $(document).ready(function(){
        $("#show_tabs").tabs();
        $('#show_tabs').show();

        var grid_page_index = $.cookie('grid_page_index') ? $.cookie('grid_page_index') : 0;
        var grid_sort_field = $.cookie('grid_sort_field') ? $.cookie('grid_sort_field') : '';
        var grid_sort_direction = $.cookie('grid_sort_direction') ? $.cookie('grid_sort_direction') : '';

        var grid_page_index_famiglia = $.cookie('grid_page_index_famiglia') ? $.cookie('grid_page_index_famiglia') : 0;
        var grid_sort_field_famiglia = $.cookie('grid_sort_field_famiglia') ? $.cookie('grid_sort_field_famiglia') : '';
        var grid_sort_direction_famiglia = $.cookie('grid_sort_direction_famiglia') ? $.cookie('grid_sort_direction_famiglia') : '';

        CLBK_get_statistiche('#ajax', grid_page_index, grid_sort_field, grid_sort_direction, $('#<?php echo $token_filter_id?>').val());
        CLBK_get_statistiche_famiglie('#statistiche_famiglia_ajax', grid_page_index_famiglia, grid_sort_field_famiglia, grid_sort_direction_famiglia, $('#<?php echo $token_filter_id_famiglia?>').val());
    });
</script>

<div class="header_sezione">
    
    <div class="titolo_sezione">
    	<h2 class="titolo_contatti"><?php echo $heading;?></h2>
    </div>


	<div class="pulsantiera">    
    		<button class="UI_buttons" onclick="CLBK_popup_chart()"><img src="<?php echo base_url().PUBLIC_ADMIN_DIR.IMAGES_DIR?>chart.png" > GRAFICO RESI</button>
       	<button class="UI_buttons" onclick="CLBK_clean_filters();"><img src="<?php echo base_url().PUBLIC_ADMIN_DIR.IMAGES_DIR?>clear.png" > RESET FILTRI</button>  
	</div>

</div>

<div id="show_tabs" style="display:none;">
    <ul>
      <li><a href="#statistiche"> Prodotti </a></li>
      <li><a href="#statistiche_famiglia"> Famiglie</a></li>
   </ul>
   <div id="statistiche">
        <input type="hidden" readonly="readonly" style="border:1px solid red; color: #000; font-weight: bold; width: 900px;" id="<?php echo $token_filter_id ?>" name="<?php echo $token_filter_id ?>" value="" />
        <div id="ajax"></div>
   </div>
   <div id="statistiche_famiglia">
        <input type="hidden" readonly="readonly" style="border:1px solid red; color: #000; font-weight: bold; width: 900px;" id="<?php echo $token_filter_id_famiglia ?>" name="<?php echo $token_filter_id_famiglia ?>" value="" />
        <div id="statistiche_famiglia_ajax"></div>
   </div>
</div>