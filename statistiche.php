<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
*
*	Author: Valerio Giacomelli
*	Email: 	valerio.giacomelli at syncronika.it
*	Web: 	www.syncronika.it
*
*/

class Statistiche extends CI_Controller {


	private $_container = null;
	private $_container_ajax = null;
	private $_denied = null;

	private $_userdata_logged = null;        
    private $_token_filter_id = null;
	private $_controller_name = '';	
	
	private $_cdr_azienda = null;        
	private $_azienda_id = null;
	private $_uid = null;
	
	
	function __construct()
	{
        parent::__construct();

        //$this->frontauth->check(array(UTENTE,UFFICIO), true, 'auth/login_form');
		$this->load->model('commesse_model');
		$this->load->model('anagrafiche_model');
		$this->load->model('files_model');
		$this->load->model('prodotti_model');
       
        //ajax grid filtering
        $this->load->library('jquery_mypagination');
        $this->load->library('ajax_filter_lib');
		$this->load->library('faac_cdr_lib');
       
	    $this->_controller_name = strtolower(get_class($this));
        $this->_token_filter_id = FILTER_ID_PREFIX.strtoupper($this->_controller_name);
        $this->_token_filter_id_famiglie = FILTER_ID_PREFIX.strtoupper($this->_controller_name).'_FAMIGLIA';
		
		$this->load->helper('mysql_date_helper');
        $this->load->helper('json');
		//$this->load->helper('my_helper');
     	//$this->load->helper('download');
           
		$this->_container  = TEMPLATE_DIR.'container';
     	$this->_container_ajax  = TEMPLATE_DIR.'container_ajax';
            
		$this->_userdata_logged = $this->frontauth->is_logged();
		$this->_uid = $this->_userdata_logged['user_id'];
		$this->_cdr_azienda = $this->anagrafiche_model->get_cdr_by_uid($this->_uid);
		// se non sono associato ad un azienda esco
		if(!is_null($this->_cdr_azienda) && count($this->_cdr_azienda) > 0){
			$this->_azienda_id = $this->_cdr_azienda['anagrafica_id'];
		}else{
			if($this->_userdata_logged['usertype'] == UTENTE){
				//$this->db_session->set_flashdata('ERROR_LOGIN', 'Il tuo utente non è stato associato ad un CDR.');
				$this->frontauth->logout();
				redirect('auth/login');					
			}
		}
		
		//$this->output->enable_profiler();
	}
        
	function index(){
		if($this->_userdata_logged['usertype'] == UTENTE or $this->_userdata_logged['usertype'] == PERSONALE){	
			redirect($this->_controller_name.'/show');
		}else if($this->_userdata_logged['usertype'] == UFFICIO){
			redirect('report/show');
		}else if($this->_userdata_logged['usertype'] == DIRIGENTE){
			redirect('statistiche/show');
		}else{
			$this->frontauth->logout();
			redirect('auth/login_form');
		}
	}

	function show(){
		$data = array(
        	'heading'           		=> 'Statistiche',
        	'token_filter_id'   		=> $this->_token_filter_id,
			'token_filter_id_famiglia'  => $this->_token_filter_id_famiglie,
       		'page'              		=> TEMPLATE_DIR.$this->_controller_name.'/show'
      	);

      	$this->load->vars($data);
       	$this->load->view($this->_container);
	}
	
	


	function show_ajax(){	
		$result = array ('esito' => false, 'logged' => true, 'messaggio' => '', 'html' => NULL);
		
		if($this->frontauth->check(DIRIGENTE, false, '')){
			
            $grid_filter_data   = $this->ajax_filter_lib->get_filter_grid_data();
            $sql_filter         = $this->ajax_filter_lib->ParseFilterTokensToFilterSql($grid_filter_data['token_filter']);

            //GRID AJAX FILTER CONFIGURATION
            $config['div']                  = '#ajax';
            $config['js_function_name']     = 'CLBK_get_'.$this->_controller_name;
            $config['js_get_filter_token']  = "$('#".$grid_filter_data["token_filter_id"]."').val()";
            $config['grid_short_field']     = $grid_filter_data['grid_sort_field'];
            $config['grid_short_direction'] = $grid_filter_data['grid_short_direction'];

            $num_rows = ($this->db_session->userdata($config['js_function_name'].'_'.$this->_controller_name.'_num_rows') != '' ? $this->db_session->userdata($config['js_function_name'].'_'.$this->_controller_name.'_num_rows') : PAG_NUM_ROWS_ADMIN);

            // conf. paginazione
            $config['base_url'] 	= site_url().'/'.$this->_controller_name.'/show_ajax/';
            $config['per_page'] 	= $num_rows;
            $config['first_link'] 	= 'Prima';
            $config['last_link']  	= 'Ultima';
            $config['uri_segment'] 	= 3;

            // ordinamento setup
            $ordersby = null;
            if(strlen($grid_filter_data['grid_sort_field']) > 0 && strlen($grid_filter_data['grid_short_direction']) > 0){
                $ordersby = array($grid_filter_data['grid_sort_field'] => $grid_filter_data['grid_short_direction']);
                $this->db_session->set_userdata($config['js_function_name'].'_'.$grid_filter_data['grid_sort_field'].'_ORDERBY', $ordersby);
            }
						
            $config['total_rows'] = $this->prodotti_model->count_all_famiglie($sql_filter['sql'], 'prod_id');
            $result = $this->prodotti_model->get_all_famiglie($config['per_page'], $this->uri->segment(3), $sql_filter['sql'],$ordersby, 'prod_id');
			
            // inizializzazione paginazione
            $this->jquery_mypagination->initialize($config);

            $data = array(
                'list'                  => $result,
                'count'                 => $config['total_rows'],
                'num_rows_selected'     => $config['per_page'],
                'userdata'				=> $this->_userdata_logged,
                'table_heading'         => $this->generate_table_heading($config['div'], $grid_filter_data['token_filter_id'], $grid_filter_data['token_filter'], $config['js_function_name'], 'CLBK_Refresh_AfterFilter', $config['js_function_name']),
                'controller_name'		=> $this->_controller_name,
                'grid_filter_data'		=> $grid_filter_data,
                'js_function_name'		=> $config['js_function_name'],
                'ajax_dest_div'			=> $config['div'],
                'pagination'            => $this->jquery_mypagination->create_links(),
                'page'                  => TEMPLATE_DIR.$this->_controller_name.'/show_ajax'
            );
			
			$result['esito'] = true;
			$result['html'] = $this->load->view($this->_container_ajax, $data, true);
		}else{
			$this->frontauth->logout();
			$result['logged'] = false;
			$result['messaggio'] = "Sessione scaduta. Si prega di rieffettuare il <a href='".base_url()."'>login</a>";
		}
		
		echo json_encode($result);
	}

	function generate_table_heading($grid_ajax_div, $token_filter_id, $token_filter, $js_set_function, $js_clear_function, $refresh_grid_function){

            $table_heading = $this->ajax_filter_lib->set_start_heading();
            $table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('ID', 'prod_id', FILTRO_NUM, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true, 'idcol');
			$table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('Nome Prodotto','prod_descrizione', FILTRO_TESTO, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true);
			$table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('Codice Materiale','prod_materiale', FILTRO_TESTO, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true);
           	$table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('Famiglia Prodotto','prod_cod_famiglia', FILTRO_TESTO, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true);
            $table_heading .= $this->ajax_filter_lib->set_grid_cell('Aggiornato il', true, 'prod_cron_data_updated', $refresh_grid_function);
            $table_heading .= $this->ajax_filter_lib->set_grid_cell('Operazioni');
            $table_heading .= $this->ajax_filter_lib->set_end_heading();

            return $table_heading;
	}
	

	function show_famiglie_ajax(){	
		$result = array ('esito' => false, 'logged' => true, 'messaggio' => '', 'html' => NULL);
		
		if($this->frontauth->check(DIRIGENTE, false, '')){
			
            $grid_filter_data   = $this->ajax_filter_lib->get_filter_grid_data();
            $sql_filter         = $this->ajax_filter_lib->ParseFilterTokensToFilterSql($grid_filter_data['token_filter']);

            //GRID AJAX FILTER CONFIGURATION
            $config['div']                  = '#statistiche_famiglia_ajax';
            $config['js_function_name']     = 'CLBK_get_statistiche_famiglie';
            $config['js_get_filter_token']  = "$('#".$grid_filter_data["token_filter_id"]."').val()";
            $config['grid_short_field']     = $grid_filter_data['grid_sort_field'];
            $config['grid_short_direction'] = $grid_filter_data['grid_short_direction'];

            $num_rows = ($this->db_session->userdata($config['js_function_name'].'_'.$this->_controller_name.'_num_rows') != '' ? $this->db_session->userdata($config['js_function_name'].'_'.$this->_controller_name.'_num_rows') : PAG_NUM_ROWS_ADMIN);

            // conf. paginazione
            $config['base_url'] 	= site_url().'/'.$this->_controller_name.'/show_ajax/';
            $config['per_page'] 	= $num_rows;
            $config['first_link'] 	= 'Prima';
            $config['last_link']  	= 'Ultima';
            $config['uri_segment'] 	= 3;

            // ordinamento setup
            $ordersby = null;
            if(strlen($grid_filter_data['grid_sort_field']) > 0 && strlen($grid_filter_data['grid_short_direction']) > 0){
                $ordersby = array($grid_filter_data['grid_sort_field'] => $grid_filter_data['grid_short_direction']);
                $this->db_session->set_userdata($config['js_function_name'].'_'.$grid_filter_data['grid_sort_field'].'_ORDERBY', $ordersby);
            }
						
            $config['total_rows'] = $this->prodotti_model->count_all_famiglie($sql_filter['sql'], 'fam_id');
            $result = $this->prodotti_model->get_all_famiglie($config['per_page'], $this->uri->segment(3), $sql_filter['sql'],$ordersby, 'fam_id');
			
            // inizializzazione paginazione
            $this->jquery_mypagination->initialize($config);

            $data = array(
                'list'                  => $result,
                'count'                 => $config['total_rows'],
                'num_rows_selected'     => $config['per_page'],
                'userdata'				=> $this->_userdata_logged,
                'table_heading'         => $this->generate_table_heading_famiglie($config['div'], $grid_filter_data['token_filter_id'], $grid_filter_data['token_filter'], $config['js_function_name'], 'CLBK_Refresh_AfterFilter_famiglie', $config['js_function_name']),
                'controller_name'		=> $this->_controller_name,
                'grid_filter_data'		=> $grid_filter_data,
                'js_function_name'		=> $config['js_function_name'],
                'ajax_dest_div'			=> $config['div'],
                'pagination'            => $this->jquery_mypagination->create_links(),
                'page'                  => TEMPLATE_DIR.$this->_controller_name.'/show_ajax_famiglie'
            );
			
			$result['esito'] = true;
			$result['html'] = $this->load->view($this->_container_ajax, $data, true);
		}else{
			$this->frontauth->logout();
			$result['logged'] = false;
			$result['messaggio'] = "Sessione scaduta. Si prega di rieffettuare il <a href='".base_url()."'>login</a>";
		}
		
		echo json_encode($result);
	}

	function generate_table_heading_famiglie($grid_ajax_div, $token_filter_id, $token_filter, $js_set_function, $js_clear_function, $refresh_grid_function){

            $table_heading = $this->ajax_filter_lib->set_start_heading();
            $table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('ID', 'fam_id', FILTRO_NUM, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true, 'idcol');
			$table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('Codice','fam_codice', FILTRO_TESTO, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true);
			$table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('Descrizione','fam_descrizione', FILTRO_TESTO, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true);
           	$table_heading .= $this->ajax_filter_lib->set_filter_grid_cell('Tipo tariffa','fam_tipo_tariffa', FILTRO_TESTO, $token_filter, $token_filter_id, $grid_ajax_div, $js_set_function, $js_clear_function, $refresh_grid_function, true);
            $table_heading .= $this->ajax_filter_lib->set_grid_cell('Operazioni');
            $table_heading .= $this->ajax_filter_lib->set_end_heading();

            return $table_heading;
	}

	function popup_chart_ajax(){
		
		$result = array ('esito' => false, 'logged' => true, 'messaggio' => '', 'html' => NULL);
		$anno_corrente = date('Y');
		$anni = array();
			
		
		$anno_selected = $this->input->post('anno_selected');
		
		$resi = array();
		
		$filter = '';
		
		for($i = 1; $i<=12; $i++){			
			$filter = 'MONTH(com_data_riparazione) = '.$i.' AND YEAR(com_data_riparazione) = '.($anno_selected > 0 ? $anno_selected : $anno_corrente).' AND com_stato_id = '.CORRETTA;	
			$reso_per_mese = $this->commesse_model->count_all($filter);
			$resi[$i] = $reso_per_mese;
		}
		
		//inizializzazione
		for($i = 0; $i<=30; $i++){
			$anni[$anno_corrente - $i] = ($anno_corrente - $i);
		}
		
		$data = array(
			'resi'					=> $resi,
			'anni'					=> $anni,
			'anno_selected'			=> $anno_selected,
			'page'					=> TEMPLATE_DIR.$this->_controller_name.'/popup/grafico_resi'
		);
			
		$result['esito'] = true;
		$result['html'] = $this->load->view($this->_container_ajax, $data, true);
		
		echo json_encode($result);
		
	}
	
		
	function get_commesse_for_json(){
		
		$this->load->helper('json');
		
		$resi = array();
		
		$anno = $this->input->post('anno_selected');
		
		for($i = 1; $i<=12; $i++){
			
			$filter = 'MONTH(com_data_riparazione) = '.$i.' AND YEAR(com_data_riparazione) = '.$anno.' AND com_stato_id = '.CORRETTA;
			
			$reso_per_mese = $this->commesse_model->count_all($filter);
			
			$resi[$i] = $reso_per_mese;
		}
		
		echo json_encode($resi);
	}
	
	
	function export_data(){		
	
		$result = array ('esito' => false, 'messaggio' => 'Problema lato server');		
		$export_session = $this->db_session->flashdata('EXPORT_SESSION');
		$mese = $this->input->post('mese');
		$anno = $this->input->post('anno');
		$ordersby = NULL;
		if(strlen($export_session) == 0){
			$result['esito'] = true;						
			$this->db_session->set_flashdata('EXPORT_SESSION', 'scarica excel');
			echo json_encode($result);
		}else{					
			$this->load->helper('to_excel');
			$list = $this->prodotti_model->get_for_year_export($anno, $ordersby);
			php_to_excel($list, $this->_controller_name.'_'.date('d-m-Y_H-i'));
		}
		
	}

	function export_data_singolo_prodotto(){		
	
		$result = array ('esito' => false, 'messaggio' => 'Problema lato server');		
		$export_session = $this->db_session->flashdata('EXPORT_SESSION');
		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('prod_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------
		$mese = $this->input->post('mese');
		$anno = $this->input->post('anno');

		$ordersby = NULL;
		if(strlen($export_session) == 0){
			$result['esito'] = true;						
			$this->db_session->set_flashdata('EXPORT_SESSION', $id_decrypted);
			$this->db_session->set_flashdata('MONTH_SESSION', $mese);
			$this->db_session->set_flashdata('YEAR_SESSION', $anno);
			echo json_encode($result);
		}else{					
			$this->load->helper('to_excel');
			$list = $this->commesse_model->get_for_product_export($export_session, NULL, NULL, 'com_stato_id = '.CORRETTA, $ordersby);
			php_to_excel($list, $this->_controller_name.'_'.date('d-m-Y_H-i'));
		}
		
	}

	function export_data_famiglia_prodotto(){		
	
		$result = array ('esito' => false, 'messaggio' => 'Problema lato server');		
		$export_session = $this->db_session->flashdata('EXPORT_SESSION');
		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('fam_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------
		$mese = $this->input->post('mese');
		$anno = $this->input->post('anno');
		$ordersby = NULL;
		if(strlen($export_session) == 0){
			$result['esito'] = true;						
			$this->db_session->set_flashdata('EXPORT_SESSION', $id_decrypted);
			echo json_encode($result);
		}else{					
			$this->load->helper('to_excel');
			$list = $this->commesse_model->get_for_family_export($export_session, $mese, $anno, 'com_stato_id = '.CORRETTA, $ordersby);
			php_to_excel($list, $this->_controller_name.'_'.date('d-m-Y_H-i'));
		}
		
	}
	
	/**************************************************************************
							GRAFICI SINGOLO PRODOTTO
	**************************************************************************/		

	function popup_statistiche_ajax(){
		
		$result = array ('esito' => false, 'logged' => true, 'messaggio' => '', 'html' => NULL);
		$anno_corrente = date('Y');
		$anni = array();

		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('prod_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------

		$item = $this->prodotti_model->get_item($id_decrypted);
		
		$commesse = array();
		
		$anno = date('Y');//'2014';
		
		$mese = date('m');
		
		$categories = array();		
		
		//generazione ascisse ordinate				
		for($i = 0; $i<30; $i++){
			
			$filter = 'MONTH(com_data_riparazione) = '.$mese.' AND YEAR(com_data_riparazione) = '.$anno.' AND com_prod_id = '.$id_decrypted.' AND com_stato_id = '.CORRETTA;
							
			$reso_per_mese = $this->commesse_model->count_all($filter);
			
			$commesse[$i] = $reso_per_mese;
			
			$categories[$i] = $mese.'.'.substr($anno,2,3);
			
			if($mese == 1){				
				$anno--;				
				$mese = 12;
					
			}else{				
				$mese--;					
			}
		
		}
				
		$cat_result = array_reverse($categories);		
		$com_result = array_reverse($commesse);		
		$res = array('series' => $com_result, 'categories' => $cat_result);
						
		//inizializzazione
		$anni[0] = 'sel';
		for($i = 0; $i<=30; $i++){
			$anni[$anno_corrente - $i] = ($anno_corrente - $i);
		}
		
		$mesi[0] = 'sel';
		for($i = 1; $i<=12; $i++){
			$mesi[$i] = $i;
		}

		$anni_statistiche = array();
		for($i = 0; $i<=30; $i++){
			$anni_statistiche[$anno_corrente - $i] = ($anno_corrente - $i);
		}

		$mesi_statistiche = array();
		for($i = 1; $i<=12; $i++){
			$mesi_statistiche[$i] = $i;
		}


		$data = array(
			'item'		=> $item,
			'res'		=> $res,
			'prod_id'	=> $id_encrypted,
			'anni'		=> $anni,
			'mesi'		=> $mesi,
			'anni_statistiche'		=> $anni_statistiche,
			'mesi_statistiche'		=> $mesi_statistiche,
			'page'		=> TEMPLATE_DIR.$this->_controller_name.'/popup/grafico_statistiche'
		);
			
		$result['esito'] = true;
		$result['html'] = $this->load->view($this->_container_ajax, $data, true);
		
		echo json_encode($result);
		
	}


	
	function get_statistiche_for_json(){
		
		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('prod_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------
		
		$commesse = array();
		
		$anno = $this->input->post('anno');
		
		$mese = $this->input->post('mese');
		
		$categories = array();		
		
		//generazione ascisse ordinate				
		for($i = 0; $i<30; $i++){
			
			$filter = 'MONTH(com_data_riparazione) = '.$mese.' AND YEAR(com_data_riparazione) = '.$anno.' AND com_prod_id = '.$id_decrypted.' AND com_stato_id = '.CORRETTA;
							
			$reso_per_mese = $this->commesse_model->count_all($filter);
			
			$commesse[$i] = $reso_per_mese;
			
			$categories[$i] = $mese.'.'.substr($anno,2,3);
			
			if($mese == 1){				
				$anno--;				
				$mese = 12;
					
			}else{				
				$mese--;					
			}
		
		}
				
		$cat_result = array_reverse($categories);		
		$com_result = array_reverse($commesse);		
		$result = array('series' => $com_result, 'categories' => $cat_result);
		
		echo json_encode($result);
	}
	
	
	function get_rientri_for_json(){
		
		$this->load->helper('json');
		
		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('prod_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------
		
		$commesse = array();
		
		$anno = $this->input->post('anno_fine');
		
		$mese = $this->input->post('mese_fine');
		
		$categories = array();
				
		for($i = 1; $i<=30; $i++){

			$lim_sup = $i*30;
			
			$lim_inf = ($i*30)-30;

			$filter = 'DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d")) <= '.$lim_sup;

			$filter.= ' AND DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d")) > '.$lim_inf;

			if(is_numeric($mese) && $mese >0){
				$filter.= ' AND DATEDIFF(DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d"), DATE_FORMAT(STR_TO_DATE("01/'.$mese.'/'.$anno.'", "%d/%m/%Y"), "%Y-%m-%d")) >= 0';
			}

			$filter.= ' AND com_prod_data REGEXP "[0-9]+"';

			$filter.= ' AND com_prod_id = '.$id_decrypted.' AND com_stato_id = '.CORRETTA;
						
			$reso_per_mese = $this->commesse_model->count_all_table($filter);
			
			$commesse[$i] = $reso_per_mese;
						
		}
		
		$result = $commesse;
				
		echo json_encode($result);
	}

	/************************************************************************
							POPUP STATISTICHE FAMIGLIA
	************************************************************************/

	function popup_statistiche_famiglia_ajax(){
		$result = array ('esito' => false, 'logged' => true, 'messaggio' => '', 'html' => NULL);
		$anno_corrente = date('Y');
		$anni = array();

		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('fam_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------

		$item = $this->prodotti_model->get_item_fam($id_decrypted);

		$commesse = array();
		
		$anno = date('Y');//'2014';
		
		$mese = date('m');
		
		$categories = array();		
		
		//generazione ascisse ordinate				
		for($i = 0; $i<30; $i++){
			
			$filter = 'MONTH(com_data_riparazione) = '.$mese.' AND YEAR(com_data_riparazione) = '.$anno.' AND fam_id = '.$id_decrypted.' AND com_stato_id = '.CORRETTA;
							
			$reso_per_mese = $this->commesse_model->count_all_prod($filter);
			
			$commesse[$i] = $reso_per_mese;
			
			$categories[$i] = $mese.'.'.substr($anno,2,3);
			
			if($mese == 1){				
				$anno--;				
				$mese = 12;
					
			}else{				
				$mese--;					
			}
		
		}
				
		$cat_result = array_reverse($categories);		
		$com_result = array_reverse($commesse);		
		$res = array('series' => $com_result, 'categories' => $cat_result);
						
		//inizializzazione
		$anni[0] = 'sel';
		for($i = 0; $i<=30; $i++){
			$anni[$anno_corrente - $i] = ($anno_corrente - $i);
		}
		
		$mesi[0] = 'sel';
		for($i = 1; $i<=12; $i++){
			$mesi[$i] = $i;
		}

		$anni_statistiche = array();
		for($i = 0; $i<=30; $i++){
			$anni_statistiche[$anno_corrente - $i] = ($anno_corrente - $i);
		}

		$mesi_statistiche = array();
		for($i = 1; $i<=12; $i++){
			$mesi_statistiche[$i] = $i;
		}

		$data = array(
			'item'				=> $item,
			'res'				=> $res,
			'fam_id'			=> $id_encrypted,
			'anni'				=> $anni,
			'mesi'				=> $mesi,
			'anni_statistiche'	=> $anni_statistiche,
			'mesi_statistiche'	=> $mesi_statistiche,
			'page'				=> TEMPLATE_DIR.$this->_controller_name.'/popup/grafico_statistiche_famiglia'
		);
			
		$result['esito'] = true;
		$result['html'] = $this->load->view($this->_container_ajax, $data, true);
		
		echo json_encode($result);
		
	}

	function get_statistiche_famiglia_for_json(){
		
		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('fam_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------
		
		$commesse = array();
		
		$anno = $this->input->post('anno');
		
		$mese = $this->input->post('mese');
		
		$categories = array();		
		
		//generazione ascisse ordinate				
		for($i = 0; $i<30; $i++){
			
			$filter = 'MONTH(com_data_riparazione) = '.$mese.' AND YEAR(com_data_riparazione) = '.$anno.' AND fam_id = '.$id_decrypted.' AND com_stato_id = '.CORRETTA;
							
			$reso_per_mese = $this->commesse_model->count_all_prod($filter);
			
			$commesse[$i] = $reso_per_mese;
			
			$categories[$i] = $mese.'.'.substr($anno,2,3);
			
			if($mese == 1){				
				$anno--;				
				$mese = 12;
					
			}else{				
				$mese--;					
			}
		
		}
				
		$cat_result = array_reverse($categories);		
		$com_result = array_reverse($commesse);		
		$result = array('series' => $com_result, 'categories' => $cat_result);
		
		echo json_encode($result);
	}
	

	function get_rientri_famiglia_for_json(){
		
		$this->load->helper('json');
		
		//----------------------------------------------------------------------------------------
		$id_encrypted = $this->input->post('fam_id'); //per sicurezza lato client e' criptato
		$id_decrypted =  $this->encrypt->decode($id_encrypted, ENCRIPTION_KEY);
		//----------------------------------------------------------------------------------------
		
		$fam_codice = $this->input->post('fam_codice');

		$commesse = array();
		
		$anno = $this->input->post('anno_fine');
		
		$mese = $this->input->post('mese_fine');
		
		$categories = array();
				
		for($i = 1; $i<=30; $i++){

			$lim_sup = $i*30;
			
			$lim_inf = ($i*30)-30;

			$filter = 'DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d")) <= '.$lim_sup;

			$filter.= ' AND DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d")) > '.$lim_inf;

			if(is_numeric($mese) && $mese >0){
				$filter.= ' AND DATEDIFF(DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d"), DATE_FORMAT(STR_TO_DATE("01/'.$mese.'/'.$anno.'", "%d/%m/%Y"), "%Y-%m-%d")) >= 0';
			}

			$filter.= ' AND fam_id = '.$id_decrypted;

			$filter.= ' AND com_prod_data REGEXP "[0-9]+"';

			$filter.= ' AND com_stato_id = '.CORRETTA;
						
			$reso_per_mese = $this->commesse_model->count_all_prod($filter);
			
			$commesse[$i] = $reso_per_mese;
						
		}
		
		$result = $commesse;
				
		echo json_encode($result);
	}
	
}

?>