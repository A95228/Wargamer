<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->library('session');
		/*cache control*/
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');

		// Set the timezone
		date_default_timezone_set(get_settings('timezone'));
	}

	public function index() {
		
		if ($this->session->userdata('user_login') == true) {
			$this->dashboard();
		}else {
			redirect(site_url('login'), 'refresh');
		}
	}

	public function dashboard() {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		$page_data['page_name'] = 'dashboard';
		$page_data['page_title'] = get_phrase('dashboard');
	
		$this->load->view('backend/index.php', $page_data);
	}

	public function matches($param1 = '', $param2 = '') {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		if ($param1 == 'add') {
			$this->crud_model->add_match();
			redirect(site_url('user/matches'), 'refresh');
		}elseif ($param1 == 'edit') {
			$this->crud_model->update_match($param2);
			redirect(site_url('user/matches'), 'refresh');
		}elseif ($param1 == 'delete') {
			$this->crud_model->delete_from_table('matches', $param2);
			$this->session->set_flashdata('flash_message', get_phrase('match_deleted'));
			redirect(site_url('user/matches'), 'refresh');
		}

		// $page_data['timestamp_start'] = strtotime('-29 days', time());
		// $page_data['timestamp_end']   = strtotime(date("m/d/Y"));
		$page_data['page_name']  = 'matches';
		$page_data['page_title'] = get_phrase('matches');
		$this->load->view('backend/index', $page_data);
	}

	public function match_form($param1 = '', $param2 = '') {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		if ($param1 == 'view') {
			$page_data['match_id'] = $param2;
			$page_data['page_name']  = 'match_view';
			$page_data['page_title'] = get_phrase('view_match');
		}
		$this->load->view('backend/index.php', $page_data);
	}


	public function rosters($param1 = "", $param2 = "") {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if ($param1 == 'add') {
			$this->user_model->add_roster();
			redirect(site_url('user/rosters'), 'refresh');
		}
		elseif ($param1 == 'view') {
			$this->user_model->view_roster($param2);
			redirect(site_url('user/rosters'), 'refresh');
		}elseif ($param1 == 'delete') {
			$this->crud_model->delete_from_table('roster', $param2);
			$this->session->set_flashdata('flash_message', get_phrase('roster_has_been_deleted'));
			redirect(site_url('user/rosters'), 'refresh');
		}

		$page_data['page_name'] = 'rosters';
		$page_data['page_title'] = get_phrase('rosters');
		
		$this->load->view('backend/index', $page_data);

	}


	public function roster_form($param1 = "", $param2 = "") {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('login'), 'refresh');
		}
		if ($param1 == 'add') {
			$page_data['page_name']  = 'roster_add';
			$page_data['page_title'] = get_phrase('roster_add');
		}elseif ($param1 == 'view') {
			$page_data['page_name']  = 'roster_view';
			$page_data['page_title'] = get_phrase('parse_roster');
//			$page_data['user_id'] = $param2;
			$page_data['roster'] = $this->user_model->view_roster($param2);
		}
		$this->load->view('backend/index.php', $page_data);
	}

	function search($param1 = "", $param2 = ""){
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if($param1 == 'start'){
			$page_data['search_button_name'] = 'stop';
			$page_data['page_name'] = 'search_form';
		}elseif($param1 == 'check'){
			$user_id = $param2;
			echo $this->user_model->check_queue($user_id);
				
			return;
			
		}elseif($param1 == 'add'){
			$match_id = $this->user_model->add_queue();
			$page_data['search_button_name'] = 'start';
			$page_data['page_name'] = 'current_match';
			if($match_id) {
				redirect(site_url('user/current_match/'.$match_id), 'refresh');
			}else{
				redirect(site_url('user/current_match'), 'refresh');
			}
			
			
		}elseif($param1 == 'stop'){
			$user_id = $param2;
			$this->user_model->delete_queue($user_id);
			$this->session->set_flashdata('flash_message', "Search deleted successfully");
			redirect(site_url('user/current_match'), 'refresh');
		}

		$this->load->view('backend/index.php', $page_data);

	}

	function review_modify($param1 = '', $param2 = '', $param3 = '', $param4 = ''){
		if ($this->session->userdata('user_login') != 1)
			redirect(site_url('login'), 'refresh');

        if($param1 == 'edit'){
        	$data['review_rating'] = $this->input->post('review_rating');
        	$data['review_comment'] = $this->input->post('review_comment');
        	$this->db->where('review_id', $param2);
        	$this->db->update('review', $data);
        	$this->session->set_flashdata('flash_message', get_phrase('review_updated_successfully'));
        }
        if($param1 == 'delete'){
            $this->db->where('review_id', $param2);
            $this->db->delete('review');
            $this->session->set_flashdata('flash_message', get_phrase('review_deleted'));
        }
        $listing_type = $this->db->get_where('listing', array('id' => $param4))->row('listing_type');
        redirect(get_listing_url($param4),'refresh');

	}

	
	function current_match($param1='', $param2 = '',$param3 = '') {
		if ($this->session->userdata('user_login') != true) {
			redirect(site_url('login'), 'refresh');
		}

		if($param1==''){
			
			$page_data['match_id'] = 0;
			
		}else if($param1 == 'save'){
			$match_id = $param2;
			$user_id = $this->session->userdata('user_id');

			$this->user_model->save_current_match($user_id);
			$this->session->set_flashdata('flash_message', get_phrase('match_saved_successfully'));
			redirect(site_url('user/current_match'), 'refresh');

		}else if($param1 == 'check'){
			$opponent_id = $param3;
			$match_id = $param2;
			echo $this->user_model->check_opponent($match_id,$opponent_id);
			return;
		}else if($param1 == 'complete'){
			$match_id = $param2;
			$this->user_model->complete_match($match_id);
			redirect(site_url('user/current_match'), 'refresh');
		}		
		else if($param1 == 'leave'){
			$this->user_model->leave_match();
			redirect(site_url('user/current_match'), 'refresh');
		}else {
			$page_data['match_id'] = $param1;
		}

		$page_data['page_name'] = 'current_match';
		$page_data['page_title'] = get_phrase('current_match');

		$this->load->view('backend/index.php', $page_data);
	}


	

		/******MANAGE OWN PROFILE AND CHANGE PASSWORD***/
    function manage_profile($param1 = '', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('user_login') != 1)
            redirect(site_url('login'), 'refresh');
        if ($param1 == 'update_profile_info') {
            $this->user_model->edit_user($param2);
            redirect(site_url('user/manage_profile'), 'refresh');
        }
        if ($param1 == 'change_password') {
            $this->user_model->change_password($param2);
            redirect(site_url('user/manage_profile'), 'refresh');
        }
        $page_data['page_name']  = 'manage_profile';
        $page_data['page_title'] = get_phrase('manage_profile');
        $page_data['user_info']  = $this->user_model->get_all_users($this->session->userdata('user_id'))->row_array();
        $this->load->view('backend/index', $page_data);
    }
}
