<script type="text/javascript">
$(document).ready(function(){
    $.cookie('grid_page_index', '<?php echo $grid_filter_data['grid_page_index']?>');
    $.cookie('grid_sort_field', '<?php echo $grid_filter_data['grid_sort_field']?>');
    $.cookie('grid_sort_direction', '<?php echo $grid_filter_data['grid_short_direction']?>');
});
</script>
<div class="content_no_tab">
    <div class="list">
        <div style="overflow: hidden; width:100%;">
            
            <div class="pagination_container">
            <div class="pagination">
                <?php echo $pagination; ?>
            </div>
            </div>
    
            <div class="filter_block">        
                <div class="ajax_num_rows_selection">
                    <label>Risultati per pagina</label>
                    <?php echo form_open('#', array('name' => 'form_num_rows_'.$controller_name, 'id' => 'form_change_num_rows_'.$controller_name)) ?>
                        <?php echo form_dropdown('num_rows', array(10 => '10', 20 => '20', 30 => '30', 50 => '50'), $num_rows_selected, 'id="'.$js_function_name.'_num_rows"') ?>
                    <?php echo form_close()?>
                </div>
                <div class="numero_record">Sono presenti <?php echo $count?> record.</div>
            </div>
        </div>
    
        <table class="gridtable">
    
        <?php echo $table_heading?>
    
            
    <?php if(!is_null($list) && is_array($list)){ ?>
    
                <?php
                        foreach($list as $item){
          
                            $filter_files = 'file_ref_id = '.$item['prod_id'].'';
                            $file_exists = ($this->files_model->count_all($filter_files) > 0 ? true : false);
    
                            $id_encrypted = $this->encrypt->encode($item['prod_id'], ENCRIPTION_KEY);
                            
                            
                            echo '<tr>';       
                            echo '<td class="idcol">';
                            echo $item['prod_id'];          
                            echo '</td><td>';
                            echo $item['prod_descrizione'];
                            echo '</td><td>';
                            echo $item['prod_materiale'];
                            echo '</td><td>';
                            echo $item['prod_cod_famiglia'];
                            echo '</td><td>';
                            echo mysqlTimeStampTostrDate($item['prod_cron_data_updated']);
                            echo '</td>';
                            echo '</td>';
                            echo '<td class="operazioni">';
                            echo '<a href="#" onclick="CBK_popup_statistiche(\''.$id_encrypted.'\')"><span class="icon-eye"></span>Visualizza Grafico</a>';
                            
                            $today = time();
                            echo '</td></tr>';
                        }
    
                ?>
    
    <?php } ?>
    
        </table>
    
        <div class="pagination">
                <?php echo $pagination; ?>
        </div>
    
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    //change num rows event
	change_num_rows_ajax('<?php echo $controller_name?>','<?php echo $js_function_name?>', '<?php echo $ajax_dest_div?>', <?php echo 0;//$grid_filter_data['grid_page_index']?>, '<?php echo $grid_filter_data['grid_sort_field']?>', '<?php echo $grid_filter_data['grid_short_direction']?>', $("#<?php echo $grid_filter_data['token_filter_id']?>").val());
});
</script>