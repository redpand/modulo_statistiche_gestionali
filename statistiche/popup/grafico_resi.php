<div id="filters_container" class="grafico-top-bar">
    <label>Anno di riferimento</label><?php echo form_dropdown('CMB_anno', $anni, '', 'id="CMB_anno"'); ?>
    <button class="UI_buttons" onclick="CLBK_export_data();">
        <img src="<?php echo base_url().PUBLIC_ADMIN_DIR.IMAGES_DIR?>excel_min.png" ><strong>Esporta</strong>
    </button>
</div>
<div id="container" class="grafico-box"></div>

<script>
$(document).ready(function(){
	
	var resi = new Array();
	
	<?php 
		foreach($resi as $key => $value){ ?>
			resi.push(<?php echo json_encode($value); ?>)
	<?php } ?>
	CBK_chart_commesse(resi);

	
	$('#CMB_anno').val('<?php echo ($anno_selected != NULL ? $anno_selected : ''); ?>');
	
	$('#CMB_anno').change(function() {
		CLBK_get_statistiche_per_anno(); return false;
	});		
})
</script>   
		
     
    
