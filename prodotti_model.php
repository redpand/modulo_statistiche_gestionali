<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
*
*	Author: Valerio Giacomelli
*	Email: 	valerio.giacomelli at syncronika.it
*	Web: 	www.syncronika.it
*
*/

class Prodotti_model extends CI_Model
{

	function __construct()
	{
		parent::__construct();
		
		$this->_table				= 'tb_sap_prodotti';
		$this->_fam_table 			= 'tb_sap_prod_famiglie';
		$this->_view	 			= 'vw_prodotti_commesse';
		$this->_primary_key			= 'prod_id';
	}
	 
	function count_all($filters = NULL){

            if(!is_null($filters) && $filters != ''){
                    $this->db->where($filters);
            }
            
            $this->db->from($this->_table);

            
            return $this->db->count_all_results();
	}
	
	function get_all($num = NULL, $offset = NULL, $filters = NULL, $ordersby = NULL){

		$result = NULL;

			$this->db->select($this->_table.'.*');
			
			if(!is_null($filters) && $filters != ''){
				$this->db->where($filters);
			}

			if(is_array($ordersby) && count($ordersby) > 0){
				foreach($ordersby as $order_by => $ordermode){
					$this->db->order_by($order_by, $ordermode);
				}
			}else{
				$this->db->order_by($this->_table.'.prod_materiale', 'ASC');
			}


			if(is_null($num) && is_null($offset)){
				$query = $this->db->get($this->_table);
			}else{
				$query = $this->db->get($this->_table, $num, $offset);
			}

			if(!is_null($query) && is_object($query)){
				$result = $query->result_array();
			}

			return $result;
	}

	function count_all_famiglie($filters = NULL, $group = NULL){

        $this->db->select($this->_view.'.*');

        if(!is_null($filters) && $filters != ''){
            $this->db->where($filters);
        }

		if(!is_null($group) && $group != ''){
            $this->db->group_by($group);
        }

        $query = $this->db->get($this->_view);

        return count($query->result());
	}

	function get_all_famiglie($num = NULL, $offset = NULL, $filters = NULL, $ordersby = NULL, $group = NULL){

		$result = NULL;

			$this->db->select($this->_view.'.*');
			
			if(!is_null($filters) && $filters != ''){
				$this->db->where($filters);
			}

			if(is_array($ordersby) && count($ordersby) > 0){
				foreach($ordersby as $order_by => $ordermode){
					$this->db->order_by($order_by, $ordermode);
				}
			}else{
				$this->db->order_by($this->_view.'.fam_codice', 'ASC');
			}

			if(!is_null($group) && $group != ''){
            	$this->db->group_by($group);
        	}

			if(is_null($num) && is_null($offset)){
				$query = $this->db->get($this->_view);
			}else{
				$query = $this->db->get($this->_view, $num, $offset);
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

	function get_item_fam($id = NULL){

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){

			$this->db->select($this->_fam_table.'.*');
			
			$this->db->from($this->_fam_table);
			$this->db->where($this->_fam_table.'.fam_id', $id);

			$query = $this->db->get();

			if(!is_null($query) && is_object($query)){
				$result = $query->row_array();
			}
		}

		return $result;
	}
	
	function get_dropdown($empty_row = true, $empty_msg = 'seleziona', $filters = null){

            $result = array();

            $this->db->select($this->_table.'.prod_id , '.$this->_table.'.prod_descrizione');
			            
            if(!is_null($filters) && $filters != ''){
                    $this->db->where($filters);
            }

            $this->db->order_by('prod_data_creazione');

            $query = $this->db->get($this->_table);

            if($empty_row){
                $result[''] = $empty_msg;
            }

            if(!is_null($query) && is_object($query)){
                foreach($query->result_array() as $row){
                    $result[$row[$this->_primary_key]] = $row['prod_descrizione'];
                }
            }

            return $result;
	}
	
		function get_dropdown_famiglie($empty_row = true, $empty_msg = 'seleziona', $filters = null){

            $result = array();

            $this->db->select($this->_fam_table.'.fam_codice , '.$this->_fam_table.'.fam_descrizione');
			            
            if(!is_null($filters) && $filters != ''){
                    $this->db->where($filters);
            }

            $this->db->order_by('fam_codice');

            $query = $this->db->get($this->_fam_table);

            if($empty_row){
                $result[''] = $empty_msg;
            }

            if(!is_null($query) && is_object($query)){
                foreach($query->result_array() as $row){
                    $result[$row['fam_codice']] = $row['fam_descrizione'];
                }
            }

            return $result;
	}  


	
	function get_item_famiglia($id = NULL){

		$result = NULL;

		if(!is_null($id) && is_numeric($id)){

			$this->db->select($this->_table.'.*, tb_sap_prod_famiglie.fam_tipo_tariffa');
			$this->db->join('tb_sap_prod_famiglie', $this->_table.'.prod_cod_famiglia = tb_sap_prod_famiglie.fam_codice');
  	
			$this->db->from($this->_table);
			$this->db->where($this->_table.'.'.$this->_primary_key, $id);

			$query = $this->db->get();

			if(!is_null($query) && is_object($query)){
				$result = $query->row_array();
			}
		}

		return $result;
	}

	function get_brands(){
		$result = array();
		
		$this->db->distinct($this->_table.'.prod_brand');
		$this->db->where('prod_brand IS NOT NULL');
		$query = $this->db->get($this->_table);
		
		if(!is_null($query) && is_object($query)){
			foreach($query->result_array() as $brand){
				$result[$brand['prod_brand']] = $brand['prod_brand'];
			}
		}

		return $result;
	}
	
	function get_for_year_export($anno = NULL, $ordersby = NULL){
		$result = NULL;
			
		$this->db->select(
			$this->_table.'.prod_id AS ID_PRODOTTO, '.
			$this->_table.'.prod_cod_famiglia AS FAMIGLIA_PRODOTTO, '.
			$this->_table.'.prod_descrizione AS PRODOTTO_DESCRIZIONE, '.
			$this->_table.'.prod_materiale AS MATERIALE_PRODOTTO, '.
			'(SELECT COUNT(*) FROM tb_commesse WHERE tb_commesse.com_prod_id = tb_sap_prodotti.prod_id) AS RESI_TOTALI'
		);
					
		//ordersby rules array
		if(!is_null($ordersby) && is_array($ordersby)){
			
			foreach($ordersby as $order_by => $ordermode){
				$this->db->order_by($order_by, $ordermode);
			}
		}else{
			$this->db->order_by($this->_table.'.prod_id', 'DESC');
		}
		
		$result = $this->db->get($this->_table);

		return $result;
	
	}	

}


?>