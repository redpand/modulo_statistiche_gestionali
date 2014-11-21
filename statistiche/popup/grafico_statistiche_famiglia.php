<script>
$(document).ready(function(){
	var data = new Date();

	/**********************************************************
						GRAFICO RESI
	**********************************************************/
	var resi = new Array();
	<?php 
		foreach($res['series'] as $key => $value){ ?>
			resi.push(<?php echo json_encode($value); ?>)
	<?php } ?>
	
	var categories = new Array();
	<?php 
		foreach($res['categories'] as $key => $value){ ?>
			categories.push(<?php echo json_encode($value); ?>)
	<?php } ?>
		
	CBK_chart_statistiche_famiglia('<?php echo $fam_id; ?>', "<?php echo $item['fam_descrizione']; ?>", resi, categories);
	
	$('#CMB_anno').val(data.getFullYear());
	
	$('#CMB_anno').change(function() {
		CBK_chart_statistiche_famiglia('<?php echo $fam_id; ?>', "<?php echo $item['fam_descrizione']; ?>", null, null); return false;
	});
	
	$('#CMB_mese').val(data.getMonth()+1);
	
	$('#CMB_mese').change(function() {
		CBK_chart_statistiche_famiglia('<?php echo $fam_id; ?>', "<?php echo $item['fam_descrizione']; ?>", null, null); return false;
	});	
	
	/**********************************************************
						GRAFICO RIENTRI'
	**********************************************************/

	CBK_chart_rientri_famiglia('<?php echo $fam_id; ?>', "<?php echo $item['fam_codice']; ?>");
	
	$('#CMB_anno_fine').val('');
	
	$('#CMB_anno_fine').change(function() {
		if($('#CMB_anno_fine').val() == 0){
			$('#CMB_mese_fine').val(0)
		}else{
			if($('#CMB_mese_fine').val() == 0){
				$('#CMB_mese_fine').val(data.getMonth()+1);
			}
		}
		CBK_chart_rientri_famiglia('<?php echo $fam_id; ?>', "<?php echo $item['fam_codice']; ?>"); return false;
	});
	
	$('#CMB_mese_fine').val('');
	
	$('#CMB_mese_fine').change(function() {
		if($('#CMB_mese_fine').val() == 0){
			$('#CMB_anno_fine').val(0)
		}else{
			if($('#CMB_anno_fine').val() == 0){
				$('#CMB_anno_fine').val(data.getFullYear());
			}
		}
		CBK_chart_rientri_famiglia('<?php echo $fam_id; ?>', "<?php echo $item['fam_codice']; ?>"); return false;
	});	
		
})
</script>  

<div class="popup">

	<div id="tabs">

		<ul>
			<li><a href="#rientri_chart">Grafico Rientri</a></li>
			<li><a href="#resi_chart">Grafico Resi</a></li>
		</ul>	

		<?php /////////////////// GRAFICO RIENTRI MATRICOLA //////////////////////// ?>
    
		<div id="rientri_chart">
	        <div id="filters_container" class="grafico-top-bar">                
				<label>Mese</label><?php echo form_dropdown('CMB_mese_fine', $mesi, '' ,'id="CMB_mese_fine"'); ?>
	            <label>Anno</label><?php echo form_dropdown('CMB_anno_fine', $anni, '', 'id="CMB_anno_fine"'); ?>

	            <button class="UI_buttons" onclick="CLBK_export_data_famiglia_prodotto('<?php echo $fam_id; ?>');">
	                <img src="<?php echo base_url().PUBLIC_ADMIN_DIR.IMAGES_DIR?>excel_min.png" ><strong>Esporta</strong>
	            </button>
	        </div>
	        <div id="rientri_famiglia_container" class="grafico-box"></div>

	    </div>

	    <?php /////////////////// GRAFICO RESI /////////////////////// ?>
        	
		<div id="resi_chart">
            <div id="filters_container" class="grafico-top-bar">
                <label>Mese</label><?php echo form_dropdown('CMB_mese', $mesi_statistiche, date('m'), 'id="CMB_mese"'); ?>
                <label>Anno</label><?php echo form_dropdown('CMB_anno', $anni_statistiche, date('Y'), 'id="CMB_anno"'); ?>
                <button class="UI_buttons" onclick="CLBK_export_data_famiglia_prodotto('<?php echo $fam_id; ?>');">
                	<img src="<?php echo base_url().PUBLIC_ADMIN_DIR.IMAGES_DIR?>excel_min.png" ><strong>Esporta</strong>
                </button>            
            </div>
            <div id="resi_container" class="grafico-box-resi"></div>

		</div>

	</div>	
        
</div> 
