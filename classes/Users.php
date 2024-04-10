<?php
require_once('../config.php');
Class Users extends DBConnection {
	private $settings;
	public function __construct(){
		global $_settings;
		$this->settings = $_settings;
		parent::__construct();
	}
	public function update_user_status(){
		extract($_POST);
		$id = $_POST['id'];
		$status = $this->conn->query("SELECT status FROM accounts WHERE id = '{$id}'")->fetch_assoc()['status'];
		$status = $status == 0 ? 1 : 0;
		$update = $this->conn->query("UPDATE `accounts` set `status` = '{$status}' where id = '{$id}'");
		if($update){
			$this->settings->set_flashdata('success','User\'s Status has been updated successfully.');
			return 1;
		}else{
			return false;
		}
	}

	public function update_user_type(){
		extract($_POST);
		$id = $_POST['id'];
		$type = $this->conn->query("SELECT account_type FROM accounts where id = '{$id}' ")->fetch_assoc()['account_type'];
		$type = $type == 0 ? 1 : 0;
		$update = $this->conn->query("UPDATE `accounts` set `account_type` = '{$type}' where id = '{$id}'");
		if($update){
			$this->settings->set_flashdata('success','User\'s Type has been updated successfully.');
			return 1;
		}else{
			return false;
		}
	}

	public function save_users(){
		if(empty($_POST['password']))
			unset($_POST['password']);
		else
		extract($_POST);
		$data = '';
		foreach($_POST as $k => $v){
				if(!empty($data)) $data .=" , ";
				$data .= " {$k} = '{$v}' ";
		}
		if(empty($_POST['id'])){
			$qry = $this->conn->query("INSERT INTO accounts set {$data}");
			if($qry){
				$id=$this->conn->insert_id;
				$this->settings->set_flashdata('success','User Details successfully saved.');
				foreach($_POST as $k => $v){
					if($k != 'id'){
						if(!empty($data)) $data .=" , ";
						if($this->settings->userdata('id') == $id)
						$this->settings->set_userdata($k,$v);
					}
				}
				return json_encode(array('status'=>'success','id'=>$id));
			}else{
				return 2;
			}
			
		}else{
			$qry = $this->conn->query("UPDATE accounts set $data where id = {$_POST['id']}");
			if($qry){
				$this->settings->set_flashdata('success','User Details successfully updated.');
				foreach($_POST as $k => $v){
					if($k != 'id'){
						if(!empty($data)) $data .=" , ";
						if($this->settings->userdata('id') == $_POST['id'])
							$this->settings->set_userdata($k,$v);
					}
				}
				return json_encode(array('status'=>'success','id'=>$_POST['id']));
			}else{
				return false;
			}
			
		}
	}
	public function delete_users(){
		extract($_POST);
		$qry = $this->conn->query("DELETE FROM users where id = $id");
		if($qry){
			$this->settings->set_flashdata('success','User Details successfully deleted.');
			return 1;
		}else{
			return false;
		}
	}
	public function save_individual(){
		if(empty($_POST['password']))
			unset($_POST['password']);
		else
		$_POST['password'] = md5($_POST['password']);
		extract($_POST);
		$data = '';
		foreach($_POST as $k => $v){
			if(!in_array($k,array('id'))){
				if(!empty($data)) $data .=" , ";
				$data .= " {$k} = '{$v}' ";
			}
		}
		if(empty($id)){
			$qry = $this->conn->query("INSERT INTO individual_list set {$data}");
			if($qry){
				$id=$this->conn->insert_id;
				$this->settings->set_flashdata('success','User Details successfully saved.');
				foreach($_POST as $k => $v){
					if($k != 'id'){
						if(!empty($data)) $data .=" , ";
						if($this->settings->userdata('id') == $id)
						$this->settings->set_userdata($k,$v);
					}
				}
				
				return 1;
			}else{
				return 2;
			}

		}else{
			$qry = $this->conn->query("UPDATE individual_list set $data where id = {$id}");
			if($qry){
				$this->settings->set_flashdata('success','User Details successfully updated.');
				foreach($_POST as $k => $v){
					if($k != 'id'){
						if(!empty($data)) $data .=" , ";
						if($this->settings->userdata('id') == $id)
							$this->settings->set_userdata($k,$v);
					}
				}
			

				return 1;
			}else{
				return "UPDATE users set $data where id = {$id}";
			}
			
		}
	}
	public function delete_individual(){
		extract($_POST);
		$qry = $this->conn->query("DELETE FROM individual_list where id = $id");
		if($qry){
			$this->settings->set_flashdata('success','Individual Details successfully deleted.');
			if(is_file(base_app."uploads/individual/$id.png"))
				unlink(base_app."uploads/individual/$id.png");
			return 1;
		}else{
			return false;
		}
	}
	function registration(){
		$_POST['password'] = md5($_POST['password']);
		extract($_POST);
		$data = "";
		$check = $this->conn->query("SELECT * FROM `individual_list` where email = '{$email}' ".($id > 0 ? " and id!='{$id}'" : "")." ")->num_rows;
		if($check > 0){
			$resp['status'] = 'failed';
			$resp['msg'] = 'Email already exists.';
			return json_encode($resp);
		}
		foreach($_POST as $k => $v){
			$v = $this->conn->real_escape_string($v);
			if(!in_array($k, ['id', 'type']) && !is_array($_POST[$k])){
				if(!empty($data)) $data .= ", ";
				$data .= " `{$k}` = '{$v}' ";
			}
		}
		if(empty($id)){
			$sql = "INSERT INTO `individual_list` set {$data} ";
		}else{
			$sql = "UPDATE set `individual_list` set {$data} where id = '{$id}' ";
		}
		$save = $this->conn->query($sql);
		if($save){
			$uid = !empty($id) ? $id : $this->conn->insert_id;
			$resp['status'] = 'success';
			if(!empty($id))
				$resp['msg'] = 'User Details has been updated successfully';
			else
				$resp['msg'] = 'Your Account has been created successfully';

		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = $this->conn->error;
			$resp['sql'] = $sql;
		}

		return json_encode($resp);
	}
	function update_user_meta(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k=>$v){
			if(!is_array($_POST[$k]) && !in_array($k,['individual_id'])){
				if(!empty($data)) $data .= ", ";
				$data .= "('{$individual_id}', '{$k}', '{$this->conn->real_escape_string($v)}')";
			}
		}
		$this->conn->query("DELETE FROM `individual_meta` where individual_id = '{$individual_id}' and meta_field in ('".(implode("','", array_keys($_POST)))."') ");
		$sql = "INSERT INTO `individual_meta` (`individual_id`, `meta_field`, `meta_value`) VALUES {$data}";
		$save = $this->conn->query($sql);
		if($save){
			$resp['status'] = 'success';
			$resp['msg'] = "Your Information for Verification has been updated successfully";
			if(isset($_FILES['vaccine_card_path']) && !empty($_FILES['vaccine_card_path']['tmp_name'])){
				$filename = "uploads/vaccines/{$individual_id}.png";
				if(!is_dir(base_app."uploads/vaccines/"))
					mkdir(base_app."uploads/vaccines/");
				$type = mime_content_type($_FILES['vaccine_card_path']['tmp_name']);
				if(!in_array($type, ['image/jpeg', 'image/png'])){
					$resp['msg'] .= ' Vaccine Card Image has failed to upload due to invalid type.';
				}else{
					if($type == 'image/png'){
						$img = imagecreatefrompng($_FILES['vaccine_card_path']['tmp_name']);
					}else{
						$img = imagecreatefromjpeg($_FILES['vaccine_card_path']['tmp_name']);
					}
					list($width, $height) = getimagesize($_FILES['vaccine_card_path']['tmp_name']);
					if($width > 640){
						$perc = ($width - 640) / $width;
						$width = 640;
						$height = $height - ($height * $perc);
					}
					if($height > 640){
						$perc = ($height - 640) / $height;
						$height = 640;
						$width = $width - ($width * $perc);
					}
					$img = imagescale($img, $width, $height);
					if(is_file(base_app.$filename))
						unlink(base_app.$filename);
					$upload = imagepng($img, base_app.$filename, 6);
					if($upload){
						$this->conn->query("DELETE FROM `individual_meta` where individual_id = '{$individual_id}' and meta_field = 'vaccine_card_path' ");
						$this->conn->query("INSERT INTO `individual_meta` set meta_field = 'vaccine_card_path', meta_value = CONCAT('{$filename}?v=',unix_timestamp(CURRENT_TIMESTAMP)), individual_id = '{$individual_id}' ");
					}else{
						$resp['msg'] .= ' Vaccine Card Image has failed to upload due to unknown reason.';
					}
				}
			}
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = $this->conn->error;
			$resp['sql'] = $sql;
		}
		if($resp['status'] && isset($resp['msg']) && !empty($resp['msg']))
			$this->settings->set_flashdata('fixed_success',$resp['msg']);
		return json_encode($resp);
	}

	function update_individual_status(){
		extract($_POST);
		$update = $this->conn->query("UPDATE `individual_list` set `status` = '{$status}' where id = '{$id}' ");
		if($update){
			$resp['status'] = 'success';
			$resp['msg'] = 'Individual\'s Status has been updated successfully.';
		}else{
			$resp['status'] = 'failed';
			$resp['msg'] = $this->conn->error;
		}
		if($resp['status'])
		$this->settings->set_flashdata('success', $resp['msg']);
		return json_encode($resp);
	}
}

$users = new users();
$action = !isset($_GET['f']) ? 'none' : strtolower($_GET['f']);
switch ($action) {
	case 'save_user':
		echo $users->save_users();
	break;
	case 'registration':
		echo $users->registration();
	break;
	case 'delete':
		echo $users->delete_users();
	break;
	case 'update_user_meta':
		echo $users->update_user_meta();
	break;
	case 'update_individual_status':
		echo $users->update_individual_status();
	break;
	case 'save_individual':
		echo $users->save_individual();
	break;
	case 'delete_individual':
		echo $users->delete_individual();
	break;
	case 'toggle_status':
		echo $users->update_user_status();
	break;
	case 'toggle_type':
		echo $users->update_user_type();
	break;
	default:
		// echo $sysset->index();
		break;
}