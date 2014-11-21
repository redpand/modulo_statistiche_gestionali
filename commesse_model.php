<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
*
*	Author: Valerio Giacomelli
*	Email: 	valerio.giacomelli at syncronika.it
*	Web: 	www.syncronika.it
*
*/

class Commesse_model extends CI_Model
{

	function __construct()
	{
		parent::__construct();
		
		$this->_table				= 'tb_commesse';
		$this->_view				= 'vw_commesse';
		$this->_view_opt			= 'vw_commesse_opt';
		$this->_view_prod			= 'vw_prodotti_commesse';
		$this->_primary_key			= 'com_id';
	}

	function generate_new_id($azienda_id = NULL){
            
            $result = null;
            
            if(!is_null($azienda_id) && is_numeric($azienda_id)){
                
                $this->db->select_max($this->_table.'.com_contatore');
                $this->db->where($this->_table.'.com_azienda_id', $azienda_id);
                
                $this->db->from($this->_table);

                $query = $this->db->get();

                if(!is_null($query) && is_object($query)){
                    $row = $query->row_array();
                    $result = (!is_null($row) && array_key_exists('com_contatore', $row) ? $row['com_contatore']+1 : null);
                }
            }
            
            return $result;
	}
	 
	function count_all($filters = NULL){

            if(!is_null($filters) && $filters != ''){
                    $this->db->where($filters);
            }
            
            $this->db->from($this->_view_opt);

            
            return $this->db->count_all_results();
	}

	function count_all_prod($filters = NULL){

            if(!is_null($filters) && $filters != ''){
                    $this->db->where($filters);
            }
            
            $this->db->from($this->_view_prod);

            
            return $this->db->count_all_results();
	}
	
	function count_all_table($filters = NULL){

            if(!is_null($filters) && $filters != ''){
                    $this->db->where($filters);
            }
            
            $this->db->from($this->_table);

            
            return $this->db->count_all_results();
	}

	
	function get_all($num = NULL, $offset = NULL, $filters = NULL, $ordersby = NULL){

			$result = NULL;

			$this->db->select($this->_view_opt.'.*');
	
			
			if(!is_null($filters) && $filters != ''){
				$this->db->where($filters);
			}

			if(is_array($ordersby) && count($ordersby) > 0){
				foreach($ordersby as $order_by => $ordermode){
					$this->db->order_by($order_by, $ordermode);
				}
			}else{
				$this->db->order_by($this->_view_opt.'.com_data_riparazione', 'DESC');
			}


			if(is_null($num) && is_null($offset)){
				$query = $this->db->get($this->_view_opt);
			}else{
				$query = $this->db->get($this->_view_opt, $num, $offset);
			}

			if(!is_null($query) && is_object($query)){
				$result = $query->result_array();
			}

			return $result;
	}

	function get_item($id = NULL){

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){

			$this->db->select($this->_table.'.*');
			
			$this->db->from($this->_table);
			
			$this->db->where($this->_table.'.'.$this->_primary_key, $id);

			$query = $this->db->get();

			if(!is_null($query) && is_object($query)){
				$result = $query->row_array();
			}
		}

		return $result;
	}

	function get_item_view_opt($id = NULL){

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){

			$this->db->select($this->_view_opt.'.*');
			
			$this->db->from($this->_view_opt);
			
			$this->db->where($this->_view_opt.'.'.$this->_primary_key, $id);
			
			$query = $this->db->get();

			if(!is_null($query) && is_object($query)){
				$result = $query->row_array();
			}
		}

		return $result;
	}

	function get_item_view($id = NULL){

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){

			$this->db->select($this->_view_opt.'.*');
			
			$this->db->from($this->_view_opt);
			
			$this->db->where($this->_view_opt.'.'.$this->_primary_key, $id);
			
			$query = $this->db->get();

			if(!is_null($query) && is_object($query)){
				$result = $query->row_array();
			}
		}

		return $result;
	}
		
	function insert($values = NULL){

		$result = false;

		if(!is_null($values) && is_array($values)){
			$this->db->trans_begin(); //transaction begin

            $values['com_contatore'] = $this->generate_new_id($values['com_azienda_id']);
            $this->db->insert($this->_table, $values);

            $trans_status = $this->db->trans_status();
            
            if($trans_status === FALSE){
                    $this->db->trans_rollback();
            }else{
                $result = $this->db->insert_id();
                $this->db->trans_commit(); //transaction commit;
            }
		}

		return $result;
	}

	function update($id = NULL, $values = NULL){

		$result = false;

		if(!is_null($id) && is_numeric($id)
			&& !is_null($values) && is_array($values)){
				$this->db->where($this->_primary_key , $id);
				$result = $this->db->update($this->_table, $values);
			}

			return $result;
	}

	function remove($id = NULL){

			$result = false;

			if(!is_null($id) && is_numeric($id)){

				$this->db->trans_begin(); //transaction begin
				
				//notifiche commessa
				$this->db->where('tb_commesse_notifiche.commesse_notifica_commessa_id', $id);
				$this->db->delete('tb_commesse_notifiche');
				
				//cambi di stato commessa
				$this->db->where('tb_cambi_stato.cambio_stato_commessa_id', $id);
				$this->db->delete('tb_cambi_stato');
				
				//associazione ricambi sap
				$this->db->where('tb_commesse_ricambi.ric_commessa_id', $id);
				$this->db->delete('tb_commesse_ricambi');
				
				//materiali di consumo
				$this->db->where('tb_commesse_mat_consumo.mat_consum_com_id', $id);
				$this->db->delete('tb_commesse_mat_consumo');
				
				$this->db->where($this->_primary_key, $id);
				$this->db->delete($this->_table);

				$trans_status = $this->db->trans_status();

				if ($trans_status === FALSE){
					$this->db->trans_rollback();
				}else{
					$result = $this->db->trans_commit(); //transaction commit;
				}
			}

			return $result;
	}
	
	
	function insert_cambio_stato($values = null){
			
		$result = false;

		if(!is_null($values) && is_array($values)){
			$this->db->insert('tb_cambi_stato', $values);
			$result = $this->db->insert_id();
		}

		return $result;	
	}
	
	function get_stati_commessa($commessa_id = null, $limit = null){
				
			$result = NULL;
			
			if(!is_null($commessa_id) && $commessa_id > 0){
				
				$this->db->select('tb_cambi_stato.*, tb_commesse_stati.stato_desc, tb_commesse_stati.stato_colore');
				
				$this->db->join('tb_commesse_stati', 'tb_cambi_stato.cambio_stato_stato_id = tb_commesse_stati.stato_id');
				
				$this->db->where('tb_cambi_stato.cambio_stato_commessa_id', $commessa_id);

				if(!is_null($limit) && is_numeric($limit)){
					$this->db->limit($limit);	
				}
	
				$this->db->order_by('tb_cambi_stato.cambio_stato_created', 'DESC');
	
				$query = $this->db->get('tb_cambi_stato');
					
				if(!is_null($query) && is_object($query)){
					$result = $query->result_array(); //(count($rows) > 0 ? $rows[0] : null);
				}				
			}

			return $result;
	}
	
	function get_stati_dropdown($empty_row = true, $empty_msg = 'seleziona'){
				
			$result = NULL;
			
			$this->db->select('tb_commesse_stati.stato_id, tb_commesse_stati.stato_desc');
	
			$query = $this->db->get('tb_commesse_stati');
					
			if($empty_row){
				$result[''] = $empty_msg;
			}
							
			if(!is_null($query) && is_object($query)){
				//$result = $query->result_array();	
				foreach ($query->result_array() as $key => $row){
					$result[$row['stato_id']] = $row['stato_desc'];
				}	
			}

			return $result;
	}
	
	function get_stato_commessa($commessa_id = null){
				
			$rows = $this->get_stati_commessa($commessa_id, 1);
			$result = (count($rows) > 0 ? $rows[0] : null);
			
			return $result;
	}
	
	
	
	//--------------------------------------------------------------------------------------------------------------
	// COMMESSE RICAMBI
	//--------------------------------------------------------------------------------------------------------------

	function count_ricambi_commessa($id = NULL) {

		$result = 0;

		if(!is_null($id) && is_numeric($id)){
            $this->db->where('tb_commesse_ricambi.ric_commessa_id', $id);
            $this->db->from('tb_commesse_ricambi');
			
            $result = $this->db->count_all_results();
		}

		return $result;
	}
	
	function get_ricambi_commessa($id = NULL) {

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){
			$this->db->select('tb_commesse_ricambi.*');
			$this->db->where('tb_commesse_ricambi.ric_commessa_id', $id);

			$query = $this->db->get('tb_commesse_ricambi');

			if(!is_null($query) && is_object($query)){
				$result = $query->result_array();
			}
		}

		return $result;
	}
	
	function sum_prezzi_ricambi_commessa($id = NULL) {

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){
			$this->db->select('(tb_commesse_ricambi.ric_prezzo * tb_commesse_ricambi.ric_qta) as totale', FALSE);
			$this->db->where('tb_commesse_ricambi.ric_commessa_id', $id);
			
			$query = $this->db->get('tb_commesse_ricambi');

			if(!is_null($query) && is_object($query)){
				$row = $query->row_array();
				$result = isset($row['totale']) ? $row['totale'] : 0;
			}
		}

		return $result;
	}
	
	function insert_ricambio_commessa($values = NULL){

		$result = false;

		if(!is_null($values) && is_array($values)){
			$this->db->insert('tb_commesse_ricambi', $values);
			$result = $this->db->insert_id();
		}

		return $result;
	}
	
	//per rimozione su update
	function remove_ricambi_commessa($id = NULL){

		$result = false;

		if(!is_null($id) && is_numeric($id)){
			//eliminazione ricambi commessa
			$this->db->where('tb_commesse_ricambi.ric_commessa_id', $id);
			$result = $this->db->delete('tb_commesse_ricambi');
		}

		return $result;
	}
	
	//--------------------------------------------------------------------------------------------------------------
	
	function sum_prezzi_materiali_commessa($id = NULL) {

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){
			$this->db->select('(tb_commesse_mat_consumo.mat_consum_prezzo * mat_consum_qta) as totale');
			$this->db->where('tb_commesse_mat_consumo.mat_consum_com_id', $id);

			$query = $this->db->get('tb_commesse_mat_consumo');

			if(!is_null($query) && is_object($query)){
				$row = $query->row_array();
				$result = isset($row['totale']) ? $row['totale'] : 0;
			}
		}

		return $result;
	}
	
	function get_materiali_commessa($id = NULL) {

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){
			$this->db->select('tb_commesse_mat_consumo.*');
			$this->db->where('tb_commesse_mat_consumo.mat_consum_com_id', $id);

			$query = $this->db->get('tb_commesse_mat_consumo');

			if(!is_null($query) && is_object($query)){
				$result = $query->result_array();
			}
		}

		return $result;
	}
	
	function insert_materiale_commessa($values = NULL){

		$result = false;

		if(!is_null($values) && is_array($values)){
			$this->db->insert('tb_commesse_mat_consumo', $values);
			$result = $this->db->insert_id();
		}

		return $result;
	}
	
	//per rimozione su update
	function remove_materiale_commessa($id = NULL){

		$result = false;

		if(!is_null($id) && is_numeric($id)){
			//eliminazione ricambi commessa
			$this->db->where('tb_commesse_mat_consumo.mat_consum_com_id', $id);
			$result = $this->db->delete('tb_commesse_mat_consumo');
		}

		return $result;
	}
	
	//--------------------------------------------------------------------------------------------------------------	
	
	function get_tipo_dropdown($empty_row = true, $empty_msg = 'seleziona'){

		$result = null;

		$this->db->select('tb_commesse_tipo.*');
		$this->db->order_by('tb_commesse_tipo.com_tipo_desc', 'ASC');

		$query = $this->db->get('tb_commesse_tipo');

		if($empty_row){
			$result[''] = $empty_msg;
		}
			
		if(!is_null($query) && is_object($query)){
			foreach ($query->result_array() as $key => $row){
				$result[$row['com_tipo_id']] = $row['com_tipo_desc'];
			}
		}
	
		return $result;
	}
	
	function get_cause_difetto_dropdown($tipo_id = NULL, $empty_row = true, $empty_msg = 'seleziona'){

		$result = null;

		$this->db->select('tb_cause_difetto.*');
		$this->db->order_by('tb_cause_difetto.causedifetto_descrizione', 'ASC');
		
		if(!is_null($tipo_id) && $tipo_id > 0){
			$this->db->where('causedifetto_tipo', $tipo_id);
		}

		$query = $this->db->get('tb_cause_difetto');

		if($empty_row){
			$result[''] = $empty_msg;
		}
			
		if(!is_null($query) && is_object($query)){
			foreach ($query->result_array() as $key => $row){
				$result[$row['causedifetto_id']] = $row['causedifetto_descrizione'];
			}
		}
	
		return $result;
	}
	
		function get_from_prodotto($prod_id = null, $json = false, $ordersby = NULL){
            
			$result = array();

			$this->db->select('*');

            if(!is_null($prod_id) && is_numeric($prod_id)){
                $this->db->where('com_prod_id', $prod_id);
            }
            
            $this->db->from($this->_table);

            if(is_array($ordersby) && count($ordersby) > 0){
                    foreach($ordersby as $order_by => $ordermode){
                            $this->db->order_by($order_by, $ordermode);
                    }
            }else{
                $this->db->order_by($this->_table.'.com_contatore', 'DESC');
            }

            $query = $this->db->get();

            if(!is_null($query) && is_object($query)){
                if($json){
                    $result = $query->result_array();
                }else{
                    foreach($query->result_array() as $key => $row){
                        $result[$row['com_data_riparazione']] = $row['com_prod_data'];
                    }
                }
            }

            return $result;
            
        }


	function get_cause_difetto($tipo_id = NULL){

		$result = null;

		$this->db->select('tb_cause_difetto.*');
		$this->db->order_by('tb_cause_difetto.causedifetto_descrizione', 'ASC');
		
		if(!is_null($tipo_id) && $tipo_id > 0){
			$this->db->where('causedifetto_tipo', $tipo_id);
		}

		$query = $this->db->get('tb_cause_difetto');
			
		if(!is_null($query) && is_object($query)){
			foreach ($query->result_array() as $key => $row){
				$result[] = $row;
			}
		}
	
		return $result;
	}
	
	/* -------------------------------------------------------------------------- */
	
	function count_notifiche($filters = NULL){
		if(!is_null($filters) && $filters != ''){
			$this->db->where($filters);
		}
		
		$this->db->from('tb_commesse_notifiche');
		return $this->db->count_all_results();
	}
	
	function get_all_notifiche($num = NULL, $offset = NULL, $filters = NULL, $ordersby = NULL){
		$result = NULL;
		
		$this->db->select('tb_commesse_notifiche.*');
		
		if(!is_null($filters) && $filters != ''){
			$this->db->where($filters);
		}
		
		if(is_array($ordersby) && count($ordersby) > 0){
			foreach($ordersby as $order_by => $ordermode){
				$this->db->order_by($order_by, $ordermode);
			}
		}else{
			$this->db->order_by('commesse_notifica_letto ASC, commesse_notifica_datetime_lettura DESC, commesse_notifica_datetime_cambiostato DESC');
		}
		
		if(is_null($num) && is_null($offset)){
			$query = $this->db->get('tb_commesse_notifiche');
		}else{
			$query = $this->db->get('tb_commesse_notifiche', $num, $offset);
		}
		
		if(!is_null($query) && is_object($query)){
			$result = $query->result_array();
		}
		
		return $result;
	}
	
	function get_all_notifiche_view($filters = NULL, $ordersby = NULL){
		$result = NULL;
		
		$this->db->select('vw_commesse_notifiche.*');
		
		if(!is_null($filters) && $filters != ''){
			$this->db->where($filters);
		}
		
		if(is_array($ordersby) && count($ordersby) > 0){
			foreach($ordersby as $order_by => $ordermode){
				$this->db->order_by($order_by, $ordermode);
			}
		}else{
			$this->db->order_by('commesse_notifica_letto ASC, commesse_notifica_datetime_lettura DESC, commesse_notifica_datetime_cambiostato DESC');
		}
		
		$query = $this->db->get('vw_commesse_notifiche');
		
		if(!is_null($query) && is_object($query)){
			$result = $query->result_array();
		}
		
		return $result;
	}

	function get_for_product_export($prod_id = NULL, $mese = NULL, $anno = NULL, $filters = NULL, $ordersby = NULL){
		$result = NULL;
			
		$this->db->select(
			$this->_view_prod.'.com_contatore AS COMMESSA_ID, '.
			'CONCAT("\'", '.$this->_view_prod.'.com_prod_matricola) AS MATRICOLA_PRODOTTO, '.
			$this->_view_prod.'.anagrafica_ragione_sociale AS CENTRO_RIPARAZIONE, '.
			$this->_view_prod.'.com_motivo_rientro AS DIFETTO, '.
			'(SELECT causedifetto_descrizione FROM tb_cause_difetto WHERE tb_cause_difetto.causedifetto_id = com_causa_difetto_id) AS CAUSA_DIFETTO, '.
			'DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%d-%m-%Y") AS DATA_MESSA_IN_PRODUZIONE, '.
			'DATE_FORMAT(com_data_riparazione, "%d-%m-%Y") AS DATA_RIPARAZIONE, '.
			'DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d")) AS DIFFERENZA_IN_GIORNI, '.
			'CEILING(DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d"))/30) AS DIFFERENZA_IN_MESI, '.
			$this->_view_prod.'.com_importo_fatturabile AS TOTALE_FATTURABILE, '.
			$this->_view_prod.'.com_prod_data_acquisto AS DATA_ACQUISTO, '.
			$this->_view_prod.'.com_data_deroga AS DATA_DEROGA_PRODOTTO, '.
			$this->_view_prod.'.com_data_deroga_cliente AS DATA_DEROGA_CLIENTE, '.
			$this->_view_prod.'.com_cliente AS CLIENTE, '.
			$this->_view_prod.'.prod_descrizione AS NOME_PRODOTTO',
			false
		);
		
		if(!is_null($prod_id) && is_numeric($prod_id)){
			$this->db->where($this->_view_prod.'.com_prod_id', $prod_id);
		}

		if(!is_null($filters) && strlen($filters) > 0){
			$this->db->where($filters);
		}

		if(!is_null($mese) && strlen($mese) > 0 && !is_null($anno) && strlen($anno) > 0){
			$this->db->where('DATEDIFF(DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d"), DATE_FORMAT(STR_TO_DATE("01/'.intval($mese).'/'.intval($anno).'", "%d/%m/%Y"), "%Y-%m-%d")) >= 0');
		}
	
		//ordersby rules array
		if(!is_null($ordersby) && is_array($ordersby)){			
			foreach($ordersby as $order_by => $ordermode){
				$this->db->order_by($order_by, $ordermode);
			}
		}else{
			$this->db->order_by($this->_view_prod.'.com_id', 'DESC');
		}
		
		$result = $this->db->get($this->_view_prod);

		return $result;
	
	}

	function get_for_family_export($fam_id = NULL, $mese = NULL, $anno = NULL, $filters = NULL, $ordersby = NULL){
		$result = NULL;
			
		$this->db->select(
			$this->_view_prod.'.com_contatore AS COMMESSA_ID, '.
			$this->_view_prod.'.fam_codice AS CODICE_FAMIGLIA, '.
			'CONCAT("\'", '.$this->_view_prod.'.com_prod_matricola) AS MATRICOLA_PRODOTTO, '.
			$this->_view_prod.'.com_motivo_rientro AS DIFETTO, '.
			'(SELECT causedifetto_descrizione FROM tb_cause_difetto WHERE tb_cause_difetto.causedifetto_id = com_causa_difetto_id) AS CAUSA_DIFETTO, '.
			'DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%d-%m-%Y") AS DATA_MESSA_IN_PRODUZIONE, '.
			'DATE_FORMAT(com_data_riparazione, "%d-%m-%Y") AS DATA_RIPARAZIONE, '.
			'DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d")) AS DIFFERENZA_IN_GIORNI, '.
			'CEILING(DATEDIFF(com_data_riparazione, DATE_FORMAT(STR_TO_DATE(CONCAT(DAY(com_data_riparazione),"/",LEFT(com_prod_data, 2),"/",RIGHT(com_prod_data, 2)), "%d/%m/%y"), "%Y-%m-%d"))/30) AS DIFFERENZA_IN_MESI, '.
			$this->_view_prod.'.com_importo_fatturabile AS TOTALE_FATTURABILE, '.
			$this->_view_prod.'.com_prod_data_acquisto AS DATA_ACQUISTO, '.
			$this->_view_prod.'.com_data_deroga AS DATA_DEROGA_PRODOTTO, '.
			$this->_view_prod.'.com_data_deroga_cliente AS DATA_DEROGA_CLIENTE, '.
			$this->_view_prod.'.com_cliente AS CLIENTE, '.
			$this->_view_prod.'.prod_descrizione AS NOME_PRODOTTO',
			false
		);
		
		if(!is_null($fam_id) && is_numeric($fam_id)){
			$this->db->where($this->_view_prod.'.fam_id', $fam_id);
		}

		if(!is_null($filters) && strlen($filters) > 0){
			$this->db->where($filters);
		}
	
		//ordersby rules array
		if(!is_null($ordersby) && is_array($ordersby)){			
			foreach($ordersby as $order_by => $ordermode){
				$this->db->order_by($order_by, $ordermode);
			}
		}else{
			$this->db->order_by($this->_view_prod.'.com_id', 'DESC');
		}
		
		$result = $this->db->get($this->_view_prod);

		return $result;
	
	}



	function get_notifica($commessa_id = NULL){
		$result = NULL;
		
		if(!is_null($commessa_id) && is_numeric($commessa_id)){
			$this->db->select('tb_commesse_notifiche.*');
			$this->db->from('tb_commesse_notifiche');
			$this->db->where('tb_commesse_notifiche.commesse_notifica_commessa_id', $commessa_id);
			
			$query = $this->db->get();
			
			if(!is_null($query) && is_object($query)){
				$result = $query->row_array();
			}
		}
		
		return $result;
	}
	
	function insert_notifica($values = NULL){
		$result = false;
		
		if(!is_null($values) && is_array($values)){
			$this->db->insert('tb_commesse_notifiche', $values);
			$result = $this->db->insert_id();
		}
		
		return $result;
	}
	
	function update_notifica($commessa_id = NULL, $values = NULL){
		$result = false;
		
		if(!is_null($commessa_id) && is_numeric($commessa_id) && !is_null($values) && is_array($values)){
			$this->db->where('tb_commesse_notifiche.commesse_notifica_commessa_id', $commessa_id);
			$result = $this->db->update('tb_commesse_notifiche', $values);
		}
		
		return $result;
	}
	

}


?>