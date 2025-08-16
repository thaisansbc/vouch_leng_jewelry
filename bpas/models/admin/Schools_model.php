<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Schools_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }
	
	public function getProductNames($term = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		$allow_category = $this->site->getCategoryByProject();
		if($allow_category){
			$this->db->where_in("products.category_id", $allow_category);
		}
		//$this->db->where('products.status !=',0);
        $wp = "( SELECT product_id, warehouse_id, quantity as quantity from {$this->db->dbprefix('warehouses_products')} ) FWP";
        $this->db->select('products.*, FWP.quantity as quantity, categories.id as category_id, categories.name as category_name', FALSE)
            ->join($wp, 'FWP.product_id=products.id', 'left')
            ->join('categories', 'categories.id=products.category_id', 'left')
			->where('products.type != ','raw_material')
			->where('products.type != ','asset')
			->where('products.type != ','problem')
            ->group_by('products.id');
		
		$where = "";
		if ($this->Settings->search_custom_field) {
			$where = " OR ({$this->db->dbprefix('products')}.cf1 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf2 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf3 LIKE '%" . $term . "%'  OR {$this->db->dbprefix('products')}.cf4 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf5 LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.cf6 LIKE '%" . $term . "%')"; 
		}
        if ($this->Settings->overselling) {
            $this->db->where("({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'".$where.")");
        } else {
            $this->db->where("(products.track_quantity = 0 OR FWP.quantity > 0) 
            	AND FWP.warehouse_id = '" .$this->Settings->default_warehouse. "' AND "
                . "({$this->db->dbprefix('products')}.name LIKE '%" . $term . "%' OR {$this->db->dbprefix('products')}.code LIKE '%" . $term . "%' OR  concat({$this->db->dbprefix('products')}.name, ' (', {$this->db->dbprefix('products')}.code, ')') LIKE '%" . $term . "%'".$where.")");
        }
        $this->db->limit($limit);
        $q = $this->db->get('products');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

	public function getClassStudentAttendances($academic_year, $biller_id, $program_id, $skill_id, $grade_id, $timeshift = null, $section_id, $class_id)
    {
		$this->db->select("sh_study_infos.*, sh_students.code, sh_students.number, CONCAT(".$this->db->dbprefix("sh_students").".lastname, ' ', ".$this->db->dbprefix("sh_students").".firstname) as student, sh_students.gender");
		$this->db->join("sh_students","sh_students.id = sh_study_infos.student_id");
		if ($academic_year) {
			$this->db->where("sh_study_infos.academic_year", $academic_year);
		}
		if ($biller_id) {
			$this->db->where("sh_study_infos.biller_id", $biller_id);
		}
		if ($program_id) {
			$this->db->where("sh_study_infos.program_id", $program_id);
		}
		if ($skill_id) {
			$this->db->where("sh_study_infos.skill_id", $skill_id);
		}
		if ($grade_id) {
			$this->db->where("sh_study_infos.grade_id", $grade_id);
		}
		if ($timeshift) {
			$this->db->where("sh_study_infos.timeshift_id", $timeshift);
		}
		if ($section_id) {
			$this->db->where("sh_study_infos.section_id", $section_id);
		}
		if ($class_id) {
			$this->db->where("sh_study_infos.class_id", $class_id);
		}
		$this->db->from("sh_study_infos");
    	$q = $this->db->get();
    	if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
    }

	public function getClassInfo($class_id = false, $academic_year = false) 
	{
		if($class_id){
			$this->db->where('class_id',$class_id);
		}
		if($academic_year){
			$this->db->where('academic_year',$academic_year);
		}
		$q = $this->db->get('sh_class_years');
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function countGender($class_id = false, $academic_year = false)
	{
		if($class_id){
			$this->db->where('sh_study_infos.class_id',$class_id);
		}
		if($class_id){
			$this->db->where('sh_study_infos.academic_year',$academic_year);
		}
		$this->db->select('gender, count(gender) as gender_qty')
				->join('sh_study_infos','sh_study_infos.student_id = sh_students.id','inner')
				->group_by('gender');
		$q = $this->db->get('sh_students');	
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;		
	}
	
	public function getStudents($no_student_id = false)
	{
		if($no_student_id){
			$this->db->where("sh_students.id !=",$no_student_id);
		}
		$q = $this->db->get('sh_students');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getcolleges(){
		$q = $this->db->get('sh_colleges');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getCourses(){
		$q = $this->db->get('sh_course');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getSkills(){
		$q = $this->db->get('sh_skills');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getScholarship(){
		$q = $this->db->get('sh_scholarships');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getSemesters(){
		$q = $this->db->get('sh_sections');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getYears(){
		$q = $this->db->get('sh_grades');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getStudentByID($id = false){
		$q = $this->db->get_where('sh_students',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	
	public function getLastStudent()
	{
		$q = $this->db->query('SELECT * FROM '.$this->db->dbprefix("sh_students").' ORDER BY id DESC LIMIT 1;');
		if($q->num_rows()>0){
			return $q->row();
		}
		return false;
	}
	
	public function addStudent($data = false, $study_info = false, $customer = false, $family = false)
	{
		$testing_id = $data["testing_student"];
		unset($data["testing_student"]);
		if($data && $this->db->insert('sh_students', $data)){
			$student_id = $this->db->insert_id();
			if ($data['number'] == $this->site->getReference('sh_std_adm')) {
				$this->site->updateReference('sh_std_adm');
			}
			if ($data['code'] == $this->site->getReference('sh_std_code')) {
				$this->site->updateReference('sh_std_code');
			}
			if(isset($data['family_id']) && $data['family_id']){
				$this->db->insert("sh_family_groups",array("family_id"=>$data['family_id'],"student_id"=>$student_id));
			} else {
				$this->db->insert("sh_family_groups",array("family_id"=>$student_id,"student_id"=>$student_id));
				$this->db->update("sh_students",array("family_id"=>$student_id),array("id"=>$student_id));
				$family_data = false;
				if($family["father"]){
					$family_data[] = array("family_id"=>$student_id,"relationship"=>"Father","full_name"=>$family["father"]);
				}
				if($family["mother"]){
					$family_data[] = array("family_id"=>$student_id,"relationship"=>"Mother","full_name"=>$family["mother"]);
				}
				if($family["guardian"]){
					$family_data[] = array("family_id"=>$student_id,"relationship"=>"Guardian","full_name"=>$family["guardian"]);
				}
				if($family_data){
					$this->db->insert_batch("sh_student_families",$family_data);
				}
			}
			if($customer){
				$customer["student_id"] = $student_id;
				$this->db->insert('companies', $customer);
			}
			if($study_info){
				$study_info["student_id"] = $student_id;
				$this->db->insert('sh_study_infos',$study_info);
				$study_id = $this->db->insert_id();
				$this->autoInvoice($study_id);
			}
			if($testing_id > 0){
				$this->db->update("sh_testings",array("student_id" => $student_id), array("id" => $testing_id));
			}
			return $student_id;
		}
		return false;
	}
	
	public function updateStudent($id = false, $data = false, $study_info = false, $customer = false)
	{
		if($id && $data && $this->db->update('sh_students',$data, array('id'=>$id))){
			if(isset($data['family_id']) && $data['family_id'] > 0){
				$this->db->update("sh_family_groups",array("family_id"=>$data['family_id']),array("student_id"=>$id));
			}
			if($customer){
				$this->db->update('companies',$customer,array('student_id'=>$id));
			}
			if($study_info){
				$this->db->update('sh_study_infos', $study_info, array("id" => $study_info["id"]));
				$this->autoInvoice($study_info["id"]);
			}
			return true;
		}
		return false;
	}
	
	public function deleteStudentByID($id = false){
		$student = $this->getStudentByID($id);
		if($id && $this->db->delete('sh_students',array('id'=>$id))){
			$this->db->delete('sh_student_documents',array('student_id'=>$id));
			$this->db->delete('sh_study_infos',array('student_id'=>$id));
			$this->db->delete('sh_student_faults',array('student_id'=>$id));
			$this->db->delete('sh_family_groups',array('student_id'=>$id));
			$this->db->delete('companies',array('student_id'=>$id));
			$siblings = $this->getSiblings($student->family_id);
			if($siblings){
				$i = 1;
				foreach($siblings as $sibling){
					$this->db->update("sh_students",array("child_no"=>$i),array("id"=>$sibling->id));
					$i++;
				}
			}
			return true;
		}
		return false;
	}
	
	public function getFamilyByID($id = false)
	{
		$q = $this->db->get_where('sh_student_families', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addFamily($data = false)
	{
		if($data && $this->db->insert('sh_student_families',$data)){
			return true;
		}
		return false;
	}
	
	public function updateFamily($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_student_families', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteFamily($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_student_families')){
			return true;
		}
		return false;
	}
	
	public function getAllRelationShips()
	{
		$q = $this->db->get('sh_relationship_types');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}	
	public function getDocumentformByID($id = false)
	{
		$q = $this->db->get_where('sh_document_forms', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	public function getDocumentStudentByID($id = false)
	{
			$this->db->select('sh_students.*')
				// ->join('sh_study_infos','sh_study_infos.student_id = sh_document_students.student_id','inner')
				->join('sh_students','sh_students.id = sh_document_students.student_id','inner')
				->where('sh_document_students.document_id', $id);
		$q = $this->db->get('sh_document_students');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return FALSE;
	}
	public function getDocumentByID($id = false)
	{
		$q = $this->db->get_where('sh_student_documents', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function addDocument($data = array())
	{
		if($this->db->insert('sh_student_documents',$data)){
			return true;
		}
		return false;
	}
	
	public function updateDocument($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('sh_student_documents', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteDocument($id = false)
	{
		if($this->db->where("id",$id)->delete('sh_student_documents')){
			return true;
		}
		return false;
	}
	
	
	public function getFaultByID($id = false)
	{
		$q = $this->db->get_where('sh_student_faults', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function addFault($data = array())
	{
		if($this->db->insert('sh_student_faults',$data)){
			return true;
		}
		return false;
	}
	
	public function updateFault($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('sh_student_faults', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteFault($id = false)
	{
		if($this->db->where("id",$id)->delete('sh_student_faults')){
			return true;
		}
		return false;
	}

	public function getStudentInfoByID($id = false)
	{
		if($id){
			$this->db->select("sh_study_infos.*,sh_programs.auto_invoice");
			$this->db->join("sh_programs","sh_programs.id = sh_study_infos.program_id","inner");
			$q = $this->db->get_where('sh_study_infos', array('sh_study_infos.id' => $id), 1);
			if ($q->num_rows() > 0) {
				return $q->row();
			}
		}
        return FALSE;
	}

	public function getStudentStudyInfoByID($id = false)
	{
		if($id){
			$this->db->select("sh_study_infos.*, sh_programs.auto_invoice, sh_skills.name as skill, sh_sections.name as section, custom_field.name as timeshift");
			$this->db->join("sh_programs","sh_programs.id = sh_study_infos.program_id","inner");
			$this->db->join("sh_skills", "sh_skills.id=sh_study_infos.skill_id", "left");
			$this->db->join("sh_sections", "sh_sections.id=sh_study_infos.section_id", "left");
			$this->db->join("custom_field", "custom_field.id=sh_study_infos.timeshift_id", "left");
			$q = $this->db->get_where('sh_study_infos', array('sh_study_infos.id' => $id), 1);
			if ($q->num_rows() > 0) {
				return $q->row();
			}
		}
        return FALSE;
	}
	
	public function addStudyInfo($data = false){
		if($data && $this->db->insert('sh_study_infos',$data)){
			$study_id = $this->db->insert_id();
			$this->autoInvoice($study_id);
			return true;
		}
		return false;
	}
	
	public function addMultiStudyInfo($data = false){
		if($data){
			foreach($data as $row){
				$this->db->insert('sh_study_infos',$row);
				$study_id = $this->db->insert_id();
				$this->autoInvoice($study_id);
			}
			return true;
		}
		return false;
	}
	
	public function updateStudyInfo($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_study_infos', $data)){
			$this->autoInvoice($id);
			return true;
		}
		return false;
	}
	
	public function deleteStudyInfo($id = false)
	{
		$study_info = $this->getStudyInfoByID($id);
		if($id && $this->db->where("id",$id)->delete('sh_study_infos')){
			return true;
		}
		return false;
	}
	
	public function addProgram($data =array())
	{
		if($data && $this->db->insert('sh_programs',$data)){
			return true;
		}
		return false;
	}
	public function getProgramByID($id = false)
	{
		$q = $this->db->get_where('sh_programs', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getProgramByCode($code)
	{
		$q = $this->db->get_where('sh_programs', array('code' => $code), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateProgram($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_programs', $data)){
			return true;
		}
		return false;
	}
	public function deleteProgramByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_programs')){
			return true;
		}
		return false;
	}
	
	public function getPrograms(){
		$q = $this->db->get_where('sh_programs',array('status'=>'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addLesson($data =array())
	{
		if($data && $this->db->insert('sh_lesson',$data)){
			return true;
		}
		return false;
	}
	public function updateLesson($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_lesson', $data)){
			return true;
		}
		return false;
	}
	public function deleteLessonByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_lesson')){
			return true;
		}
		return false;
	}
	public function getLessonByID($id = false)
	{
		$q = $this->db->get_where('sh_lesson', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getLessonByCourseID($lesson_id=false)
	{
		$q = $this->db->get_where('sh_lesson',array('course_id'=>$lesson_id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function addCourse($data =array())
	{
		if($data && $this->db->insert('sh_course',$data)){
			return true;
		}
		return false;
	}
	public function updateCourse($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_course', $data)){
			return true;
		}
		return false;
	}
	public function getCourseByID($id = false)
	{
		$q = $this->db->get_where('sh_course', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function deleteCourseByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_course')){
			return true;
		}
		return false;
	}

	public function addGrade($data =array())
	{
		if($data && $this->db->insert('sh_grades',$data)){
			return true;
		}
		return false;
	}
	
	public function getGradeByID($id = false)
	{
		$q = $this->db->get_where('sh_grades', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function updateGrade($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_grades', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteGradeByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_grades')){
			return true;
		}
		return false;
	}
	
	public function getGrades()
	{
		$this->db->order_by("(".$this->db->dbprefix('sh_grades').".name + 0)");
		$q = $this->db->get_where('sh_grades',array('status'=>'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getGraduateGrades($status = false)
	{
		if($status == "graduate"){
			$this->db->where("graduate",1);
		}
		$this->db->order_by("(".$this->db->dbprefix('sh_grades').".name + 0)");
		$q = $this->db->get_where('sh_grades',array('status'=>'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getBillers() {
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('id',$this->session->userdata('biller_id'));
		}
		$this->db->order_by("order_no");
		$this->db->where('group_name','biller');
        $q = $this->db->get('companies');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }


	
	public function addRoom($data =array())
	{
		if($data && $this->db->insert('sh_rooms',$data)){
			return true;
		}
		return false;
	}
	
	public function getRoomByID($id = false)
	{
		$q = $this->db->get_where('sh_rooms', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateRoom($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_rooms', $data)){
			return true;
		}
		return false;
	}
	public function deleteRoomByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_rooms')){
			return true;
		}
		return false;
	}
	
	public function getRooms($biller_id = false){
		if($biller_id){
			$this->db->where('sh_rooms.biller_id',$biller_id);
		}
		$q = $this->db->get_where('sh_rooms',array('status'=>'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	
	public function addClass($data =array())
	{
		if($data && $this->db->insert('sh_classes',$data)){
			return true;
		}
		return false;
	}
	
	public function getClassByID($id = false)
	{
		$q = $this->db->get_where('sh_classes', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateClass($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_classes', $data)){
			return true;
		}
		return false;
	}
	public function deleteClassByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_classes')){
			$this->db->delete('sh_table_times',array('class_id'=>$id));
			return true;
		}
		return false;
	}
	
	
	public function addSubject($data =array())
	{
		if($data && $this->db->insert('sh_subjects',$data)){
			return true;
		}
		return false;
	}
	
	public function getSubjectByID($id = false)
	{
		$q = $this->db->get_where('sh_subjects', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateSubject($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_subjects', $data)){
			return true;
		}
		return false;
	}
	public function deleteSubjectByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_subjects')){
			return true;
		}
		return false;
	}
	
	public function getSubjects(){
		$q = $this->db->get_where('sh_subjects',array('status'=>'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getClasses($biller_id = false, $program_id = false, $grade_id = false){
		if($biller_id){
			$this->db->where('sh_classes.biller_id',$biller_id);
		}
		if($program_id){
			$this->db->where('sh_classes.program_id',$program_id);
		}
		if($grade_id){
			$this->db->where('sh_classes.grade_id',$grade_id);
		}
		$q = $this->db->get_where('sh_classes',array('status'=>'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	
	public function addSection($data =array())
	{
		if($data && $this->db->insert('sh_sections',$data)){
			return true;
		}
		return false;
	}
	public function getSection()
	{
		$q = $this->db->get("sh_sections");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
	}
	public function getSectionByID($id = false)
	{
		$q = $this->db->get_where('sh_sections', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateSection($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_sections', $data)){
			return true;
		}
		return false;
	}
	public function deleteSectionByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_sections')){
			return true;
		}
		return false;
	}
	
	public function getSectionByProgramGrade($program_id = false, $grade_id = false, $skill_id = false)
	{
		// var_dump($skill_id);exit;
		if($program_id && $grade_id){
			if($skill_id){
				$this->db->where('skill_id', $program_id);
			}
			$q = $this->db->get_where('sh_sections',array(
					'program_id'=>$program_id,
					'grade_id'=>$grade_id
				));
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		return false;
	}
	public function getSubjectByIDs($ids){
		if($ids){
			$this->db->where_in('id',$ids)
					->where('status','active');
			$q = $this->db->get('sh_subjects');
			if($q->num_rows() > 0 ){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		return false;
	}
	
	public function addTimeTable($data = false){
		if($data && $this->db->insert('sh_table_times',$data)){
			return true;
		}
		return false;
	}
	public function updateTimeTable($id = false, $data = array())
	{

		if($id && $data && $this->db->where("id",$id)->update('sh_table_times', $data)){
			return true;
		}
		return false;
	}
	public function deleteTimeTableByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_table_times')){
			return true;
		}
		return false;
	}
	
	public function getTimeTableByID($id = false)
	{
		$q = $this->db->get_where('sh_table_times', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function addClassYear($data = false)
	{
		if($data && $this->db->insert('sh_class_years',$data)){
			return true;
		}
		return false;
	}
	public function updateClassYear($id = false, $data = array())
	{

		if($id && $data && $this->db->where("id",$id)->update('sh_class_years', $data)){
			return true;
		}
		return false;
	}
	public function deleteClassYearByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_class_years')){
			return true;
		}
		return false;
	}
	
	public function getClassYearByID($id = false)
	{
		$q = $this->db->get_where('sh_class_years', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	
	public function getTotalStudentByClass($class_id = false){
		if($class_id){
			$q = $this->db->query("
									SELECT
										count( ".$this->db->dbprefix('sh_study_infos').".class_id ) AS total_student 
									FROM
										".$this->db->dbprefix('sh_students')."
										INNER JOIN ".$this->db->dbprefix('sh_study_infos')." ON ".$this->db->dbprefix('sh_study_infos').".student_id = ".$this->db->dbprefix('sh_students').".id 
									WHERE
										".$this->db->dbprefix('sh_students').".`status` = 'active' 
									AND ".$this->db->dbprefix('sh_study_infos').".class_id = ".$class_id."
								");
			if($q->num_rows() > 0){
				return $q->row();
			}					
		}
		return false;
	}
	
	
	public function getTeacherByID($id = false){
		$q = $this->db->get_where('sh_teachers', array('id' => $id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	
	public function getLastTeacher()
	{
		$q = $this->db->query('SELECT * FROM '.$this->db->dbprefix("sh_teachers").' ORDER BY id DESC LIMIT 1;');
		if($q->num_rows()>0){
			return $q->row();
		}
		return false;
	}
	
	public function addTeacher($data = false){
		if($data && $this->db->insert('sh_teachers',$data)){
			return $this->db->insert_id();
		}
		return false;
	}
	
	public function updateTeacher($id = false, $data = false){
		if($id && $data && $this->db->update('sh_teachers',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	
	public function deleteTeacherByID($id = false){
		if($id && $this->db->delete('sh_teachers',array('id'=>$id))){
			$this->db->delete('sh_teach_infos',array('teacher_id'=>$id));
			$this->db->delete('sh_teacher_documents',array('teacher_id'=>$id));
			$this->db->delete('sh_teacher_qualifications',array('teacher_id'=>$id));
			$this->db->delete('sh_teacher_working_histories',array('teacher_id'=>$id));
			$this->db->delete('sh_teachers_families',array('teacher_id'=>$id));
			return true;
		}
		return false;
	}
	
	public function getTeachers($biller_id = false){
		if($biller_id){
			$this->db->where('sh_teachers.biller_id',$biller_id);
		}
		$q = $this->db->get_where('sh_teachers',array('status'=>'active'));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getTcFamilyByID($id = false)
	{
		$q = $this->db->get_where('sh_teachers_families', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addTcFamily($data =array())
	{
		if($data && $this->db->insert('sh_teachers_families',$data)){
			return true;
		}
		return false;
	}
	
	public function updateTcFamily($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_teachers_families', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteTcFamily($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_teachers_families')){
			return true;
		}
		return false;
	}
	
	
	public function getTcDocumentByID($id = false)
	{
		$q = $this->db->get_where('sh_teacher_documents', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return FALSE;
	}
	
	public function addTcDocument($data = array())
	{
		if($this->db->insert('sh_teacher_documents',$data)){
			return true;
		}
		return false;
	}
	
	public function updateTcDocument($id = false, $data =array())
	{
		if($this->db->where("id",$id)->update('sh_teacher_documents', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteTcDocument($id = false)
	{
		if($this->db->where("id",$id)->delete('sh_teacher_documents')){
			return true;
		}
		return false;
	}
	
	public function getTeachInfoByID($id = false)
	{
		$q = $this->db->get_where('sh_teach_infos', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addTeachInfo($data = false){
		if($data && $this->db->insert('sh_teach_infos',$data)){
			return true;
		}
		return false;
	}
	
	public function updateTeachInfo($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_teach_infos', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteTeachInfo($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_teach_infos')){
			return true;
		}
		return false;
	}
	
	public function getTeacherInfoByID($id = false)
	{
		$q = $this->db->get_where('sh_teach_infos', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getGradeByIDs($ids = false){
		if($ids){
			$this->db->where_in('sh_grades.id',$ids);
			$this->db->select('sh_grades.id,sh_grades.name');
			$q = $this->db->get('sh_grades');
			if($q->num_rows() > 0 ){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		return false;
	}
	
	public function getTeacherBySubject($subject = false, $biller_id = false){
		if($subject){
			$this->db->where('sh_teach_infos.subject_id',$subject);
		}
		if($biller_id){
			$this->db->where('sh_teachers.biller_id',$biller_id);
		}
		$this->db->select('sh_teachers.id, sh_teachers.lastname, sh_teachers.firstname,sh_teach_infos.grade_id')
				->join('sh_teach_infos','sh_teachers.id = sh_teach_infos.teacher_id','inner')
				->where('sh_teachers.status','active');
				
		$q = $this->db->get('sh_teachers');		
		if($q->num_rows() > 0 ){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
	
		return false;
	}
	
	
	public function addQualification($data =array())
	{
		if($this->db->insert('sh_teacher_qualifications',$data)){
			return true;
		}
		return false;
	}
	
	public function updateQualification($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update('sh_teacher_qualifications', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteQualification($id = false)
	{
		if($this->db->where("id",$id)->delete('sh_teacher_qualifications')){
			return true;
		}
		return false;
	}
	
	public function getQualificationByID($id= NULL)
	{
		$q = $this->db->get_where('sh_teacher_qualifications', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addWorkingHistory($data =array())
	{
		if($this->db->insert('sh_teacher_working_histories',$data)){
			return true;
		}
		return false;
	}
	
	public function getWorkingHistoryByID($id= NULL)
	{
		$q = $this->db->get_where('sh_teacher_working_histories', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function updateWorkingHistory($id = false, $data = array())
	{
		if($this->db->where("id",$id)->update('sh_teacher_working_histories', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteWorkingHistory($id = false)
	{
		if($this->db->where("id",$id)->delete('sh_teacher_working_histories')){
			return true;
		}
		return false;
	}
	
	public function getStudentByClassID($class_id = false, $academic_year = false){
		if($class_id){
			if($academic_year){
				$this->db->where('sh_study_infos.academic_year', $academic_year);
			}
			$this->db->select('sh_students.*');
			$this->db->where('sh_study_infos.class_id', $class_id);
			$this->db->join('sh_study_infos','sh_study_infos.student_id = sh_students.id','inner');
			$q = $this->db->get('sh_students');
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$data[] = $row;
				}
				return $data;
			}
		}
		return false;
	}
	
	public function getStudentClassByStudent($student_id = false, $class_id = false, $academic_year = false){
		$q = $this->db->get_where('sh_study_infos',array('student_id' => $student_id,'class_id'=>$class_id,'academic_year'=>$academic_year));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addExamination($data = false, $items = false){
		
		if($data && $this->db->insert('sh_examinations',$data)){
			$examination_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item['examination_id'] = $examination_id;
					$this->db->insert('sh_examination_items',$item);
				}
			}
			$this->generateScore($examination_id, $data['class_id'],$data['section_id'],$data['academic_year'],$data['year'],$data['month'],$data['final']);
			return true;
		}
		return false;
	}
	
	public function updateExamination($id=false, $data = false, $items = false){
		if($data && $id){
			if($this->db->update('sh_examinations',$data, array('id'=>$id))){
				$this->db->delete('sh_examination_items',array('examination_id'=>$id));
				if($items){
					$this->db->insert_batch('sh_examination_items',$items);
				}
				$this->generateScore($id, $data['class_id'],$data['section_id'],$data['academic_year'],$data['year'],$data['month'],$data['final']);
				return true;
			}
		}
		return false;
	}
	
	public function deleteExamination($id = false)
	{
		$exam = $this->getExaminationByID($id);
		if($id && $this->db->where("id",$id)->delete('sh_examinations')){
			$this->db->delete('sh_examination_items',array('examination_id'=>$id));
			$this->generateScore($id, $exam->class_id,$exam->section_id,$exam->academic_year,$exam->year,$exam->month,$exam->final);
			return true;
		}
		return false;
	}
	
	public function addExaminationExcel($data = false){
		if($data && $this->db->insert('sh_examinations',$data)){
			return true;
		}
		return false;
	}
	
	public function getExaminationByID($id = false)
    {
        $q = $this->db->get_where("sh_examinations", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function updateExaminationDetail($id = false, $data = false){
		if($this->db->update('sh_examination_items',$data, array('id'=>$id))){
			$exam_detail = $this->getExaminationDetailByID($id);
			$exam = $this->getExaminationByID($exam_detail->examination_id);
			$this->generateScore($exam->id, $exam->class_id,$exam->section_id,$exam->academic_year,$exam->year,$exam->month,$exam->final);
			return true;
		}
		return false;
	}
	
	public function deleteExaminationDetail($id = false)
	{
		$exam_detail = $this->getExaminationDetailByID($id);
		$exam = $this->getExaminationByID($exam_detail->examination_id);
		if($id && $this->db->where("id",$id)->delete('sh_examination_items')){
			$this->generateScore($exam->id, $exam->class_id,$exam->section_id,$exam->academic_year,$exam->year,$exam->month,$exam->final);
			return true;
		}
		return false;
	}
	
	public function getExaminationDetailByID($id = false){
		$q = $this->db->get_where('sh_examination_items',array('id'=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getExaminationDetail($id = false){
		$this->db->select("	
							sh_examination_items.id,
							concat(".$this->db->dbprefix("sh_students").".lastname,' ',".$this->db->dbprefix("sh_students").".firstname) as student,
							sh_examinations.academic_year,
							sh_examinations.year,
							sh_examinations.month,
							sh_examinations.class_name,
							sh_subjects.name as subject_name,
							sh_examination_items.score,
							IF(final = 1,'yes','no') as final
							")
						->join("sh_examinations","sh_examinations.id = sh_examination_items.examination_id","inner")
						->join("sh_students","sh_students.id = sh_examination_items.student_id","left")
						->join("sh_subjects","sh_subjects.id = sh_examination_items.subject_id","left")
						->where("sh_examination_items.id",$id);
		$q = $this->db->get("sh_examination_items");		
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}	
	
	public function getStudentByCode($code = false)
	{
		if($code){
			$q = $this->db->get_where('sh_students',array('code'=>$code));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}

	public function getStudentByNumber($number = false)
	{
		if($number){
			$q = $this->db->get_where('sh_students', array('number' => $number));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	
	public function getTeacherByCode($code = false){
		if($code){
			$q = $this->db->get_where('sh_teachers',array('code'=>$code));
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	
	public function finalizeExamination($id = false, $data = false, $items = false)
    {
        if ($this->db->update('sh_examinations', $data, array('id' => $id))) {
            if($items){
				$this->db->insert_batch('sh_examination_items',$items);
			}
			$exam = $this->getExaminationByID($id);
			$this->generateScore($id, $exam->class_id,$exam->section_id,$exam->academic_year,$exam->year,$exam->month, $exam->final);
            return TRUE;
        }
        return FALSE;
    }
	
	public function getExaminationItems($id = false){
		$this->db->select('sh_examination_items.*,sh_students.code,sh_students.lastname,sh_students.firstname,sh_subjects.name as subject_name');
		$this->db->join('sh_students','sh_students.id = sh_examination_items.student_id','left');
		$this->db->join('sh_subjects','sh_subjects.id = sh_examination_items.subject_id','left');
		//$this->db->where('sh_examination_items.score !=',0);
		$q = $this->db->get_where('sh_examination_items',array('examination_id'=>$id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}

	public function getStudentNames($term = false, $class_id = false, $academic_year = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		if($class_id){
			$this->db->join('sh_study_infos','sh_study_infos.student_id = sh_students.id','inner');
			$this->db->where('sh_study_infos.class_id',$class_id);
			if($academic_year){
				$this->db->where('sh_study_infos.academic_year',$academic_year);
			}
		} 
		$this->db->where('sh_students.status','active');

		
        $this->db->select('sh_students.*')
            ->where("(lastname LIKE '%" . $term . "%' OR firstname LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
				concat(lastname,' ',firstname,' ', ' (', code, ')') LIKE '%" . $term . "%')")
            ->group_by('sh_students.id')->limit($limit);
			
        $q = $this->db->get('sh_students');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
    public function getTeacherNames($term = false, $class_id = false, $academic_year = false, $limit = false)
    {
		$limit = $this->Settings->rows_per_page;
		// if($class_id){
		// 	$this->db->join('sh_study_infos','sh_study_infos.student_id = sh_students.id','inner');
		// 	$this->db->where('sh_study_infos.class_id',$class_id);
		// 	if($academic_year){
		// 		$this->db->where('sh_study_infos.academic_year',$academic_year);
		// 	}
		// } 
		// $this->db->where('sh_students.status','active');
        $this->db->select('sh_teachers.*')
            ->where("(lastname LIKE '%" . $term . "%' OR firstname LIKE '%" . $term . "%' OR code LIKE '%" . $term . "%' OR
				concat(lastname,' ',firstname,' ', ' (', code, ')') LIKE '%" . $term . "%')")
            ->group_by('sh_teachers.id')->limit($limit);
			
        $q = $this->db->get('sh_teachers');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

	
	public function getCreditScores($program_id = false){
		if($program_id){
			$this->db->where('program_id',$program_id);
		}
		$q = $this->db->get('sh_credit_scores');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function addCreditScore($data =array())
	{
		if($data && $this->db->insert('sh_credit_scores',$data)){
			return true;
		}
		return false;
	}
	
	public function getCreditScoreByID($id = false)
	{
		$q = $this->db->get_where('sh_credit_scores', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateCreditScore($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_credit_scores', $data)){
			return true;
		}
		return false;
	}
	public function deleteCreditScore($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_credit_scores')){
			return true;
		}
		return false;
	}

	public function countBillerStudent($student_ids = false){
		if($student_ids){
			$this->db->where_in('id',$student_ids);
			$this->db->group_by('biller_id');
			$this->db->select('biller_id');
			$q = $this->db->get('sh_students');
			if ($q->num_rows() == 1) {
				return $q->row();
			}
		}
		return false;
		
	}
	

	
	public function generateScore($exam_id = false, $class_id = false, $section_id = false, $academic_year = false, $year = false, $month = false, $final = false){
		if($class_id && $section_id && $academic_year && $year && $month){
			$this->db->delete('sh_scores',array('exam_id'=>$exam_id));
			$this->db->delete('sh_scores',array('class_id'=>$class_id,'section_id'=>$section_id,'academic_year'=>$academic_year,'year'=>$year,'month'=>$month,'final'=>$final));
			$class = $this->getClassByID($class_id);
			$section = $this->getSectionByID($section_id);
			$credit_scores = $this->getCreditScores($class->program_id);
			$this->db->select('	
							sh_examinations.id as exam_id,
							sh_examinations.academic_year,
							sh_examinations.year,
							sh_examinations.month,
							sh_examinations.final,
							sh_students.biller_id,
							sh_students.biller as biller_name,
							sh_students.id as student_id, 
							sh_programs.id as program_id,
							sh_programs.name as program_name,
							sh_sections.id as section_id,
							sh_sections.name as section_name,
							sh_grades.id as grade_id,
							sh_grades.name as grade_name,
							sh_classes.id as class_id,
							sh_classes.name as class_name,
							sh_subjects.id as subject_id,
							sh_subjects.name as subject_name,
							max('.$this->db->dbprefix('sh_examination_items').'.score) as score,
							FIND_IN_SET(max('.$this->db->dbprefix('sh_examination_items').'.score), ranks.rank ) AS rank,
							"N/A"  as credit,
							"#333333"  as color
							')
				->join('sh_examination_items','sh_examination_items.examination_id = sh_examinations.id','inner')
				->join('sh_subjects','sh_subjects.id = sh_examination_items.subject_id','inner')
				->join('sh_students','sh_students.id = sh_examination_items.student_id','inner')
				->join('sh_classes','sh_classes.id = sh_examinations.class_id','inner')
				->join('sh_sections','sh_sections.id = sh_examinations.section_id','inner')
				->join('sh_programs','sh_programs.id = sh_classes.program_id','inner')
				->join('sh_grades','sh_grades.id = sh_classes.grade_id','inner')
				->join('(
							SELECT
								subject_id,
								GROUP_CONCAT( max_score ORDER BY max_score DESC ) AS rank 
							FROM
							(
								SELECT
									'.$this->db->dbprefix("sh_examination_items").'.subject_id,
									max('.$this->db->dbprefix('sh_examination_items').'.score) as max_score
								FROM
									'.$this->db->dbprefix("sh_examination_items").'
									INNER JOIN '.$this->db->dbprefix("sh_examinations").' ON '.$this->db->dbprefix("sh_examinations").'.id = '.$this->db->dbprefix("sh_examination_items").'.examination_id
								WHERE
									'.$this->db->dbprefix("sh_examinations").'.class_id = '.$class_id.'
								AND	'.$this->db->dbprefix("sh_examinations").'.section_id = '.$section_id.'
								AND	'.$this->db->dbprefix("sh_examinations").'.academic_year = "'.$academic_year.'"
								AND	'.$this->db->dbprefix("sh_examinations").'.year = "'.$year.'"
								AND	'.$this->db->dbprefix("sh_examinations").'.month = "'.$month.'"
								AND	'.$this->db->dbprefix("sh_examinations").'.final = "'.$final.'"
								GROUP BY
									'.$this->db->dbprefix("sh_examination_items").'.subject_id,
									'.$this->db->dbprefix("sh_examination_items").'.student_id
							) as tmp_rank
							GROUP BY subject_id
						) as ranks','ranks.subject_id = sh_examination_items.subject_id','inner'
				)
				->where('sh_examination_items.score >', 0)
				->where('sh_examinations.class_id', $class_id)
				->where('sh_examinations.section_id', $section_id)
				->where('sh_examinations.academic_year', $academic_year)
				->where('sh_examinations.year', $year)
				->where('sh_examinations.month', $month)
				->where('sh_examinations.final', $final)
				->group_by('sh_examination_items.student_id,sh_examination_items.subject_id');
			$q = $this->db->get('sh_examinations');
			$data = false;
			$subject_data = false;
			if($q->num_rows() > 0){
				foreach($q->result() as $row){
					$subject_data[$row->subject_id] = $row->subject_id;
					if($credit_scores){
						foreach($credit_scores as $credit_score){
							$grade_ids = json_decode($credit_score->grade_id);
							$subject_ids = json_decode($credit_score->subject_id);
							if($row->score >= $credit_score->min_score && $row->score <= $credit_score->max_score && in_array($class->grade_id,$grade_ids) && in_array($row->subject_id,$subject_ids)){
								$row->credit = $credit_score->credit;
								$row->color = $credit_score->color;
							}
						}
					}
					$data[] = (array) $row;
				}
				if($data){
					$this->db->insert_batch('sh_scores',$data);
				}
				
			}
			$this->db->delete('sh_student_monthly',array('exam_id'=>$exam_id));
			$this->db->delete('sh_student_monthly',array('class_id'=>$class_id,'section_id'=>$section_id,'academic_year'=>$academic_year,'year'=>$year,'month'=>$month, 'final'=>$final));
			$this->db->select('
								sh_scores.exam_id,
								sh_scores.academic_year,
								sh_scores.`year`,
								sh_scores.`month`,
								sh_scores.`final`,
								sh_scores.biller_id,
								sh_scores.biller_name,
								sh_scores.student_id,
								sh_scores.program_id,
								sh_scores.program_name,
								sh_scores.section_id,
								sh_scores.section_name,
								sh_scores.grade_id,
								sh_scores.grade_name,
								sh_scores.class_id,
								sh_scores.class_name,
								sum( '.$this->db->dbprefix('sh_scores').'.score ) AS score,
								FIND_IN_SET(sum( '.$this->db->dbprefix('sh_scores').'.score ), ranks.rank ) AS rank')
					->join('(
							SELECT
								class_id,
								GROUP_CONCAT( total_score ORDER BY total_score DESC ) AS rank 
							FROM
							(
								SELECT
									'.$this->db->dbprefix("sh_scores").'.class_id,
									sum('.$this->db->dbprefix('sh_scores').'.score) as total_score
								FROM
									'.$this->db->dbprefix("sh_scores").'
								WHERE
									'.$this->db->dbprefix("sh_scores").'.class_id = '.$class_id.'
								AND	'.$this->db->dbprefix("sh_scores").'.section_id = '.$section_id.'
								AND	'.$this->db->dbprefix("sh_scores").'.academic_year = "'.$academic_year.'"
								AND	'.$this->db->dbprefix("sh_scores").'.year = "'.$year.'"
								AND	'.$this->db->dbprefix("sh_scores").'.month = "'.$month.'"
								AND	'.$this->db->dbprefix("sh_scores").'.final = "'.$final.'"
								GROUP BY
									'.$this->db->dbprefix("sh_scores").'.student_id
							) as tmp_rank
							GROUP BY class_id
						) as ranks','ranks.class_id = sh_scores.class_id','inner')		
					->where('sh_scores.year',$year)
					->where('sh_scores.academic_year',$academic_year)
					->where('sh_scores.month',$month)
					->where('sh_scores.final',$final)
					->where('sh_scores.section_id',$section_id)
					->where('sh_scores.class_id',$class_id)
					->group_by('sh_scores.student_id');
			$q = $this->db->get('sh_scores');						
			if($q->num_rows() > 0){
				$student_data = false;
				if($section->score_type=='average'){
					$qty_subject = (count($subject_data) > 0 ?  count($subject_data) : 1);
					foreach($q->result_array() as $row){
						$result = '';
						$row['average'] = $row['score'] / $qty_subject;
						if($row['average'] < 5){
							$result = 'fail';
						}else if($row['average'] >= 5 && $row['average'] < 6.5){
							$result = 'average';
						}else if($row['average'] >= 6.5 && $row['average'] < 8){
							$result = 'ogood';
						}else{
							$result = 'ngood';
						}
						$row['result'] = $result;
						$student_data[] = $row;
					}
				}else{
					if($section){
						$total_score = 0;
						$s_subject_ids = $subject_data;
						if($s_subject_ids){
							foreach($s_subject_ids as $subject_id){
								if($credit_scores){
									foreach($credit_scores as $credit_score){
										$grade_ids = json_decode($credit_score->grade_id);
										$subject_ids = json_decode($credit_score->subject_id);
										if(in_array($class->grade_id,$grade_ids) && in_array($subject_id,$subject_ids)){
											$total_score += $credit_score->cal_score;
										}
									}
								}
							}
						}
						if($total_score > 0){
							$average_score = $total_score / 50;
							foreach($q->result_array() as $row){
								$result = '';
								$credit = '';
								$row['average'] = $row['score'] / $average_score;
								if($row['average'] < 25){
									$result = 'fail';
								}else if($row['average'] >= 25 && $row['average'] <= 32.5){
									$result = 'average';
								}else if($row['average'] > 32.5 && $row['average'] < 40){
									$result = 'ogood';
								}else{
									$result = 'ngood';
								}

								if($row['average'] < 25){
									$credit = 'F';
								}else if($row['average'] >= 25 && $row['average'] < 30){
									$credit = 'E';
								}else if($row['average'] >= 30 && $row['average'] < 35){
									$credit = 'D';
								}else if($row['average'] >= 35 && $row['average'] < 40){
									$credit = 'C';
								}else if($row['average'] >= 40 && $row['average'] < 45){
									$credit = 'B';
								}else{
									$credit = 'A';
								}
								
								$row['result'] = $result;
								$row['credit'] = $credit;
								$student_data[] = $row;
							}
						}
					}
				}
				if($student_data){
					$this->db->insert_batch('sh_student_monthly',$student_data);
				}
			}
			$this->db->delete('sh_student_sectionly',array('exam_id'=>$exam_id));
			$this->db->delete('sh_student_sectionly',array('class_id'=>$class_id,'section_id'=>$section_id,'academic_year'=>$academic_year));
			$this->db->select('
								(sum( '.$this->db->dbprefix("sh_student_monthly").'.average ) / count( '.$this->db->dbprefix("sh_student_monthly").'.id )) AS monthly_average,
								((sections.average + (sum( '.$this->db->dbprefix("sh_student_monthly").'.average ) / count( '.$this->db->dbprefix("sh_student_monthly").'.id ))) / 2) AS average,
								sh_student_monthly.exam_id,
								sh_student_monthly.academic_year,
								sh_student_monthly.biller_id,
								sh_student_monthly.biller_name,
								sh_student_monthly.student_id,
								sh_student_monthly.program_id,
								sh_student_monthly.program_name,
								sh_student_monthly.section_id,
								sh_student_monthly.section_name,
								sh_student_monthly.grade_id,
								sh_student_monthly.grade_name,
								sh_student_monthly.class_id,
								sh_student_monthly.class_name
								')
					->join('(SELECT
								'.$this->db->dbprefix("sh_student_monthly").'.student_id,
								'.$this->db->dbprefix("sh_student_monthly").'.average 
							FROM
								'.$this->db->dbprefix("sh_student_monthly").'
							WHERE
								'.$this->db->dbprefix("sh_student_monthly").'.academic_year = "'.$academic_year.'"
								AND '.$this->db->dbprefix("sh_student_monthly").'.final = 1 
								AND '.$this->db->dbprefix("sh_student_monthly").'.section_id = '.$section_id.' 
								AND '.$this->db->dbprefix("sh_student_monthly").'.class_id = '.$class_id.' 
							GROUP BY
								'.$this->db->dbprefix("sh_student_monthly").'.student_id) as sections','sections.student_id = sh_student_monthly.student_id','inner')
					->where('sh_student_monthly.academic_year',$academic_year)
					->where('sh_student_monthly.final',0)
					->where('sh_student_monthly.section_id',$section_id)
					->where('sh_student_monthly.class_id',$class_id)
					->group_by('sh_student_monthly.student_id');
			$q = $this->db->get('sh_student_monthly');		
			
			
			if($q->num_rows() > 0){
				$montlhy_data = false;
				$data = $q->result_array();
				arsort($data);
				$old_value = 0;
				$old_rank = 1;
				$i = 1;
				foreach($data as $montlhy_value){
					if($montlhy_value['monthly_average'] != $old_value){
						$old_rank = $i;
					}
					$i++;
					$old_value = $montlhy_value['monthly_average'];
					unset($montlhy_value['monthly_average']);
					$montlhy_value['monthly_rank'] = $old_rank;
					$montlhy_value['monthly_average'] = $old_value;
					$montlhy_data[] = $montlhy_value;
				}
				
				arsort($montlhy_data);
				$old_value = 0;
				$old_rank = 1;
				$i = 1;
				foreach($montlhy_data as $sectionly_value){
					if($sectionly_value['average'] != $old_value){
						$old_rank = $i;
					}
					$i++;
					$old_value = $sectionly_value['average'];
					$sectionly_value['rank'] = $old_rank;
					$sectionly_data[] = $sectionly_value;
					
				}
				if($sectionly_data){
					$student_data = false;
					if($section->score_type=='average'){
						foreach($sectionly_data as $row){
							$result = '';
							$row['average'] = $row['average'];
							if($row['average'] < 5){
								$result = 'fail';
							}else if($row['average'] >= 5 && $row['average'] < 6.5){
								$result = 'average';
							}else if($row['average'] >= 6.5 && $row['average'] < 8){
								$result = 'ogood';
							}else{
								$result = 'ngood';
							}
							$row['result'] = $result;
							$student_data[] = $row;
						}
					}else{
						foreach($sectionly_data as $row){
							$result = '';
							$credit = '';
							$row['average'] = $row['average'];
							if($row['average'] < 25){
								$result = 'fail';
							}else if($row['average'] >= 25 && $row['average'] <= 32.5){
								$result = 'average';
							}else if($row['average'] > 32.5 && $row['average'] < 40){
								$result = 'ogood';
							}else{
								$result = 'ngood';
							}
							if($row['average'] < 25){
								$credit = 'F';
							}else if($row['average'] >= 25 && $row['average'] < 30){
								$credit = 'E';
							}else if($row['average'] >= 30 && $row['average'] < 35){
								$credit = 'D';
							}else if($row['average'] >= 35 && $row['average'] < 40){
								$credit = 'C';
							}else if($row['average'] >= 40 && $row['average'] < 45){
								$credit = 'B';
							}else{
								$credit = 'A';
							}		
							$row['result'] = $result;
							$row['credit'] = $credit;
							$student_data[] = $row;
						}
					}
					if($student_data){
						$this->db->insert_batch('sh_student_sectionly',$student_data);
					}
				}
			}
			
			$section_qty = count($this->getSectionByProgramGrade($class->program_id,$class->grade_id));
			$this->db->delete('sh_student_yearly',array('exam_id'=>$exam_id));
			$this->db->delete('sh_student_yearly',array('class_id'=>$class_id,'academic_year'=>$academic_year));
			$this->db->select('
								(sum( '.$this->db->dbprefix("sh_student_sectionly").'.average ) / '.$section_qty.') AS average,
								(sum( '.$this->db->dbprefix("sh_student_sectionly").'.monthly_average ) / '.$section_qty.') AS section_average,
								student_monthly.month_average,
								student_monthly.month_score,
								sh_student_sectionly.exam_id,
								sh_student_sectionly.academic_year,
								sh_student_sectionly.biller_id,
								sh_student_sectionly.biller_name,
								sh_student_sectionly.student_id,
								sh_student_sectionly.program_id,
								sh_student_sectionly.program_name,
								sh_student_sectionly.grade_id,
								sh_student_sectionly.grade_name,
								sh_student_sectionly.class_id,
								sh_student_sectionly.class_name')
					->where('sh_student_sectionly.academic_year',$academic_year)
					->where('sh_student_sectionly.class_id',$class_id)
					->join('(
							SELECT 
								student_id,
								(sum( '.$this->db->dbprefix("sh_student_monthly").'.average ) / '.$section_qty.') AS month_average,
								(sum( '.$this->db->dbprefix("sh_student_monthly").'.score ) / '.$section_qty.') AS month_score
							FROM 
								'.$this->db->dbprefix("sh_student_monthly").'
							WHERE 	
								final = 1
							AND class_id = '.$class_id.'
							AND academic_year = "'.$academic_year.'"
							GROUP BY
								student_id
							) as student_monthly','student_monthly.student_id = sh_student_sectionly.student_id','LEFT')
					->group_by('sh_student_sectionly.student_id');
			$q = $this->db->get('sh_student_sectionly');		
			if($q->num_rows() > 0){
				$yearly_data = false;
				$year_data = false;
				$section_data = false;
				$data = $q->result_array();
				arsort($data);
				$old_value = 0;
				$old_rank = 1;
				$i = 1;
				foreach($data as $row){
					if($row['average'] != $old_value){
						$old_rank = $i;
					}
					$i++;
					$old_value = $row['average'];
					unset($row['average']);
					$row['rank'] = $old_rank;
					$row['average'] = $old_value;
					$year_data[] = $row;
				}
				
				arsort($year_data);
				$old_value = 0;
				$old_rank = 1;
				$i = 1;
				foreach($year_data as $row){
					if($row['section_average'] != $old_value){
						$old_rank = $i;
					}
					$i++;
					$old_value = $row['section_average'];
					unset($row['section_average']);
					$row['section_rank'] = $old_rank;
					$row['section_average'] = $old_value;
					$section_data[] = $row;
				}
				
				arsort($section_data);
				$old_value = 0;
				$old_rank = 1;
				$i = 1;
				foreach($section_data as $row){
					if($row['month_average'] != $old_value){
						$old_rank = $i;
					}
					$i++;
					$old_value = $row['month_average'];
					$row['month_rank'] = $old_rank;
					$yearly_data[] = $row;
				}

				if($yearly_data){
					$student_data = false;
					if($section->score_type=='average'){
						foreach($yearly_data as $row){
							$result = '';
							$row['average'] = $row['average'];
							if($row['average'] < 5){
								$result = 'fail';
							}else if($row['average'] >= 5 && $row['average'] < 6.5){
								$result = 'average';
							}else if($row['average'] >= 6.5 && $row['average'] < 8){
								$result = 'ogood';
							}else{
								$result = 'ngood';
							}
							$row['result'] = $result;
							$student_data[] = $row;
						}
					}else{
						foreach($yearly_data as $row){
							$result = '';
							$credit = '';
							$row['average'] = $row['average'];
							if($row['average'] < 25){
								$result = 'fail';
							}else if($row['average'] >= 25 && $row['average'] <= 32.5){
								$result = 'average';
							}else if($row['average'] > 32.5 && $row['average'] < 40){
								$result = 'ogood';
							}else{
								$result = 'ngood';
							}
							if($row['average'] < 25){
								$credit = 'F';
							}else if($row['average'] >= 25 && $row['average'] < 30){
								$credit = 'E';
							}else if($row['average'] >= 30 && $row['average'] < 35){
								$credit = 'D';
							}else if($row['average'] >= 35 && $row['average'] < 40){
								$credit = 'C';
							}else if($row['average'] >= 40 && $row['average'] < 45){
								$credit = 'B';
							}else{
								$credit = 'A';
							}		
							$row['result'] = $result;
							$row['credit'] = $credit;
							$student_data[] = $row;
						}
					}
					if($student_data){
						$this->db->insert_batch('sh_student_yearly',$student_data);
					}
				}
			}
			
			
			$this->db->delete('sh_score_yearly',array('exam_id'=>$exam_id));
			$this->db->delete('sh_score_yearly',array('class_id'=>$class_id,'academic_year'=>$academic_year));
			$this->db->select('
								(sum('.$this->db->dbprefix('sh_scores').'.score) / '.$section_qty.') as average,
								sh_scores.exam_id,
								sh_scores.academic_year,
								sh_scores.biller_id,
								sh_scores.biller_name,
								sh_scores.student_id,
								sh_scores.program_id,
								sh_scores.program_name,
								sh_scores.grade_id,
								sh_scores.grade_name,
								sh_scores.class_id,
								sh_scores.class_name,
								sh_scores.subject_id,
								sh_scores.subject_name
								')
					->where('sh_scores.academic_year',$academic_year)
					->where('sh_scores.class_id',$class_id)
					->where('sh_scores.final',1)
					->group_by('sh_scores.student_id,sh_scores.subject_id');
			$q = $this->db->get('sh_scores');
			if($q->num_rows() > 0){
				$subject_datas = false;
				$student_datas = false;
				$data = false;
				foreach($q->result_array() as $row){
					$subject_datas[$row['subject_id']] = $row['subject_name'];
					$student_datas[$row['subject_id']][$row['student_id']] = $row;
				}
				
				if($subject_datas){
					foreach($subject_datas as $subject_index => $subject_value){
						if($student_datas[$subject_index]){
							arsort($student_datas[$subject_index]);
							$old_value = 0;
							$old_rank = 1;
							$i = 1;
							foreach($student_datas[$subject_index] as $row){
								if($row['average'] != $old_value){
									$old_rank = $i;
								}
								$i++;
								$old_value = $row['average'];
								$row['rank'] = $old_rank;
								$data[] = $row;
							}
						}
					}
				}
				
				if($data){
					$this->db->insert_batch('sh_score_yearly',$data);
				}
			}
			
			
			return true;
		}
		return false;
	}
	
	
	public function getMonthlyScores($biller = false,$program = false,$grade = false,$academic_year = false, $year = false,$month = false,$class = false,$student = false, $final = false){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_scores.biller_id',$this->session->userdata('biller_id'));
		}
		if($biller){
			$this->db->where('sh_scores.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_scores.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_scores.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_scores.academic_year',$academic_year);
		}
		if($year){
			$this->db->where('sh_scores.year',$year);
		}
		if($month){
			$this->db->where('sh_scores.month',$month);
		}
		if($class){
			$this->db->where('sh_scores.class_id',$class);
		}
		if($student){
			$this->db->where('sh_scores.student_id',$student);
		}
		if((int)$final!=3){
			if($final){
				$this->db->where('sh_scores.final',1);
			}else{
				$this->db->where('sh_scores.final',0);
			}
		}
		
		$this->db->select('sh_scores.*, sh_students.lastname,sh_students.firstname,sh_students.gender, sh_students.code, sh_students.number as student_number')
				->join('sh_students','sh_students.id = sh_scores.student_id','left')
				->order_by('sh_scores.section_name,class_name,sh_scores.year,sh_scores.month,sh_scores.final,sh_scores.subject_name,sh_students.lastname');
		$q = $this->db->get('sh_scores');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getMonthlyStudentScores($biller = false,$program = false,$grade = false,$academic_year = false,$year = false,$month = false,$class = false,$student = false , $final = false){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_student_monthly.biller_id',$this->session->userdata('biller_id'));
		}
		if($biller){
			$this->db->where('sh_student_monthly.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_student_monthly.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_student_monthly.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_student_monthly.academic_year',$academic_year);
		}
		if($year){
			$this->db->where('sh_student_monthly.year',$year);
		}
		if($month){
			$this->db->where('sh_student_monthly.month',$month);
		}
		if($class){
			$this->db->where('sh_student_monthly.class_id',$class);
		}
		if($student){
			$this->db->where('sh_student_monthly.student_id',$student);
		}

		
		if((int)$final!=3){
			if($final){
				$this->db->where('sh_student_monthly.final',1);
			}else{
				$this->db->where('sh_student_monthly.final',0);
			}
		}

		$this->db->select('sh_student_monthly.*, sh_students.lastname,sh_students.firstname,sh_students.gender,sh_students.photo');
		$this->db->join('sh_students','sh_students.id = sh_student_monthly.student_id','left');
		$this->db->order_by('sh_student_monthly.section_name,sh_student_monthly.class_name,sh_student_monthly.year,sh_student_monthly.month,sh_student_monthly.final,sh_students.lastname');
		$q = $this->db->get('sh_student_monthly');
		if($q->num_rows () > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getSectionlyStudentScores($biller = false,$program = false,$grade = false,$academic_year = false, $class = false,$student = false){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_student_sectionly.biller_id',$this->session->userdata('biller_id'));
		}
		if($biller){
			$this->db->where('sh_student_sectionly.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_student_sectionly.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_student_sectionly.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_student_sectionly.academic_year',$academic_year);
		}
		if($class){
			$this->db->where('sh_student_sectionly.class_id',$class);
		}
		if($student){
			$this->db->where('sh_student_sectionly.student_id',$student);
		}
		$this->db->select('sh_student_sectionly.*, sh_students.lastname,sh_students.firstname,sh_students.gender');
		$this->db->join('sh_students','sh_students.id = sh_student_sectionly.student_id','left');
		$this->db->order_by('sh_student_sectionly.program_name,sh_student_sectionly.section_name,grade_name,class_name,sh_students.lastname');
		$q = $this->db->get('sh_student_sectionly');
		if($q->num_rows () > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getYearlyStudentScores($biller = false,$program = false,$grade = false,$academic_year = false, $class = false,$student = false){
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_student_yearly.biller_id',$this->session->userdata('biller_id'));
		}
		if($biller){
			$this->db->where('sh_student_yearly.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_student_yearly.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_student_yearly.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_student_yearly.academic_year',$academic_year);
		}
		if($class){
			$this->db->where('sh_student_yearly.class_id',$class);
		}
		if($student){
			$this->db->where('sh_student_yearly.student_id',$student);
		}
		$this->db->select('sh_student_yearly.*, sh_students.lastname,sh_students.firstname,sh_students.gender');
		$this->db->join('sh_students','sh_students.id = sh_student_yearly.student_id','left');
		$this->db->order_by('sh_student_yearly.program_name,grade_name,class_name,sh_students.lastname');
		$q = $this->db->get('sh_student_yearly');
		if($q->num_rows () > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getYearlyScores($biller = false,$program = false,$grade = false,$academic_year = false, $class = false,$student = false){
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_score_yearly.biller_id',$this->session->userdata('biller_id'));
		}
		if($biller){
			$this->db->where('sh_score_yearly.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_score_yearly.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_score_yearly.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_score_yearly.academic_year',$academic_year);
		}
		if($class){
			$this->db->where('sh_score_yearly.class_id',$class);
		}
		if($student){
			$this->db->where('sh_score_yearly.student_id',$student);
		}
		$this->db->select('sh_score_yearly.*, sh_students.lastname,sh_students.firstname,sh_students.gender');
		$this->db->join('sh_students','sh_students.id = sh_score_yearly.student_id','left');
		$this->db->order_by('sh_score_yearly.program_name,grade_name,class_name,sh_score_yearly.subject_name, sh_students.lastname');
		$q = $this->db->get('sh_score_yearly');
		if($q->num_rows () > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getScoreClass($biller = false,$program = false,$grade = false,$academic_year = false,$year = false,$month = false,$class = false,$student = false , $final = false){
		
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_student_monthly.biller_id',$this->session->userdata('biller_id'));
		}
		
		if($biller){
			$this->db->where('sh_student_monthly.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_student_monthly.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_student_monthly.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_student_monthly.academic_year',$academic_year);
		}
		if($year){
			$this->db->where('sh_student_monthly.year',$year);
		}
		if($month){
			$this->db->where('sh_student_monthly.month',$month);
		}
		if($class){
			$this->db->where('sh_student_monthly.class_id',$class);
		}
		if($student){
			$this->db->where('sh_student_monthly.student_id',$student);
		}
		if($final){
			$this->db->where('sh_student_monthly.final',1);
		}else{
			$this->db->where('sh_student_monthly.final',0);
		}
		
		$this->db->select('class_id,class_name');
		$this->db->group_by('sh_student_monthly.class_id');
		$q = $this->db->get_where('sh_student_monthly',array('academic_year'=>$academic_year));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
	
		return false;
	}
	
	public function getExamination($academic_year = false, $year = false, $month = false, $class_id = false, $section_id = false, $final = false){
		if($academic_year && $year && $month && $class_id && $section_id){
				$this->db->where('sh_examinations.academic_year',$academic_year)
					->where('sh_examinations.year',$year)
					->where('sh_examinations.month',$month)
					->where('sh_examinations.class_id',$class_id)
					->where('sh_examinations.section_id',$section_id)
					->where('sh_examinations.final',$final);
			$q = $this->db->get('sh_examinations');
			if($q->num_rows()){
				return $q->row();
			}
		}
		return false;
	}
	
	
	public function checkExaminationDetail($student_id = false, $subject_id = false, $academic_year = false, $year = false, $month = false, $class_id = false, $section_id = false, $final = false, $score = false){
		if($student_id && $subject_id && $academic_year && $year && $month && $class_id && $section_id && $score){
			$this->db->select('sh_students.firstname,sh_students.lastname,sh_subjects.`name` AS subject_name, sh_examination_items.score')
					->join('sh_examinations','sh_examinations.id = sh_examination_items.examination_id','inner')
					->join('sh_students','sh_students.id = sh_examination_items.student_id','inner')
					->join('sh_subjects','sh_subjects.id = sh_examination_items.subject_id','inner')
					->where('sh_examination_items.score !=', $score)
					->where('sh_examination_items.student_id',$student_id)
					->where('sh_examination_items.subject_id',$subject_id)
					->where('sh_examinations.academic_year',$academic_year)
					->where('sh_examinations.year',$year)
					->where('sh_examinations.month',$month)
					->where('sh_examinations.class_id',$class_id)
					->where('sh_examinations.section_id',$section_id)
					->where('sh_examinations.final',$final);
			$q = $this->db->get('sh_examination_items');	
			
			if($q->num_rows() > 0){
				return $q->row();
			}
		}
		return false;
	}
	
	public function getBillerByName($name = false){
		$q = $this->db->get_where('companies',array('group_name'=>'biller','name'=>$name));
		if($q->num_rows()){
			return $q->row();
		}
		return false;
	}
	public function getAreaByCode($code = false){
		$q = $this->db->get_where('zones',array('zone_code'=>$code));
		if($q->num_rows()){
			return $q->row();
		}
		return false;
	}
	public function getAreaByName($name = false){
		$this->db->select('zones.*');
		$this->db->where('zones.zone_name',"$name");
		$q = $this->db->get('zones');
		
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function importStudent($students = false, $customers = false)
	{
		if (!empty($students)) {
			foreach ($students as $key => $student) {
				$this->db->insert('sh_students', $student);
				$student_id = $this->db->insert_id();
				if(isset($customers[$key])){
					$customers[$key]["student_id"] = $student_id;
					$this->db->insert('companies', $customers[$key]);
				}
			}
			return true;
		}
		return false;
	}
	public function importTeacher($teachers = false){
		if($teachers && $this->db->insert_batch('sh_teachers',$teachers)){
			return true;
		}
		return false;
	}
	
	public function getStudentInfoByStudent($student_id = false, $academic_year = false){
		if($academic_year){
			$this->db->where("academic_year",$academic_year);
		}
		$this->db->where('student_id',$student_id);
		$this->db->group_by('class_id');
		$q = $this->db->get('sh_study_infos');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function checkStudentBlacklist($student_id = false, $class_id = false, $academic_year = false){
		$this->db->where('student_id',$student_id)
				->where('class_id',$class_id)
				->where('academic_year',$academic_year)
				->where('blacklist',1);
		$q = $this->db->get('sh_student_faults');
		if($q->num_rows() > 0){
			return true;
		}
		return false;
	}
	public function getAttendanceByID($id = false)
    {
        $q = $this->db->get_where("sh_attendances", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

	public function addAttendanceStudent($data = false, $items = false){
		if($data && $this->db->insert("sh_attendances",$data)){
			$id_card_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["attendance_id"] = $id_card_id;
					$this->db->insert("sh_attendance_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function addAttendanceTeacher($data = false, $items = false){
		if($data && $this->db->insert("sh_teacher_attendances",$data)){
			$id_card_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["attendance_id"] = $id_card_id;
					$this->db->insert("sh_teacher_attendance_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	public function addAttendanceExcel($data = false){
		if($data && $this->db->insert('sh_attendances',$data)){
			return true;
		}
		return false;
	}
	// public function deleteAttendance($id = false)
	// {
	// 	if($id){
	// 		$this->db->delete('sh_attendance_items',array('id'=>$id));
	// 		return true;
	// 	}
	// 	return false;
	// }


	public function deleteAttendance($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_attendances')){
			$this->db->delete('sh_attendance_items',array('attendance_id'=>$id));
			return true;
		}
		return false;
	}
	
	public function finalizeAttendance($id = false, $data = false, $items = false)
    {
        if ($this->db->update('sh_attendances', $data, array('id' => $id))) {
            if($items){
				$this->db->insert_batch('sh_attendance_items',$items);
			}
            return TRUE;
        }
        return FALSE;
    }
	
	public function getAttendanceItems($id = false){
		$this->db->select('sh_attendance_items.*,companies.code,companies.name');
		$this->db->join('companies','companies.id = sh_attendance_items.student_id','left');
		$q = $this->db->get_where('sh_attendance_items',array('attendance_id'=>$id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getClassess($biller = false, $program = false, $grade = false, $academic_year = false, $class = false)
	{	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_students.biller_id',$this->session->userdata('biller_id'));
		}
		
		if($biller){
			$this->db->where('sh_students.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_study_infos.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_study_infos.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_study_infos.academic_year',$academic_year);
		}
		if($class){
			$this->db->where('sh_study_infos.class_id',$class);
		}

		$this->db->select('sh_study_infos.class_id,sh_study_infos.class as class_name, sh_study_infos.academic_year, sh_study_infos.program, companies.name as biller_name, sh_class_years.teacher_name');
		$this->db->join('sh_class_years','sh_class_years.class_id = sh_study_infos.class_id AND sh_class_years.academic_year = sh_study_infos.academic_year','left');
		$this->db->join('sh_students','sh_students.id = sh_study_infos.student_id','left');
		$this->db->join('companies','companies.id = sh_students.biller_id','left');
		$this->db->group_by('sh_study_infos.class_id');
		$q = $this->db->get('sh_study_infos');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
	
		return false;
	}
	
	public function getFamilyByStudent($student_id = false, $type = "family"){
		if($type){
			$this->db->where("sh_student_families.type",$type);
		}
		$this->db->where("sh_students.id",$student_id);
		$this->db->select("sh_student_families.*");
		$this->db->join("sh_students","sh_students.family_id = sh_student_families.family_id","INNER");
		$q = $this->db->get("sh_student_families");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
	
		return false;
	}
	
	public function getStudentAttendances($biller = false,$program = false, $grade= false, $academic_year = false, $year = false, $month = false, $class = false, $student = false)
	{	
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) {
			$this->db->where('sh_attendances.biller_id',$this->session->userdata('biller_id'));
		}	
		if($biller){
			$this->db->where('sh_attendances.biller_id',$biller);
		}
		if($program){
			$this->db->where('sh_attendances.program_id',$program);
		}
		if($grade){
			$this->db->where('sh_attendances.grade_id',$grade);
		}
		if($academic_year){
			$this->db->where('sh_attendances.academic_year',$academic_year);
		}
		if($year){
			$this->db->where('sh_attendances.year',$year);
		}
		if($month){
			$this->db->where('sh_attendances.month',$month);
		}
		if($class){
			$this->db->where('sh_attendances.class_id',$class);
		}
		if($student){
			$this->db->where('sh_attendance_items.student_id',$student);
		}
		$this->db->select('
							sh_attendances.biller_id,
							sh_attendance_items.student_id,
							sh_attendances.section_id,
							sh_attendances.program_id,
							sh_attendances.grade_id,
							sh_attendances.class_id,
							sh_attendances.academic_year,
							sum('.$this->db->dbprefix('sh_attendance_items').'.absent) as absent,
							sum('.$this->db->dbprefix('sh_attendance_items').'.permission) as permission,
							sum('.$this->db->dbprefix('sh_attendance_items').'.late) as late
						');

		$this->db->join('sh_attendance_items','sh_attendance_items.attendance_id = sh_attendances.id','inner');				
		$this->db->group_by('sh_attendance_items.student_id,sh_attendances.section_id,sh_attendances.class_id,sh_attendances.academic_year,sh_attendances.biller_id,sh_attendances.program_id,sh_attendances.grade_id');
		$q = $this->db->get('sh_attendances');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
	
		return false;
	}
	
	public function getTeacherAttendanceByID($id = false)
    {
        $q = $this->db->get_where("sh_teacher_attendances", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
	
	public function addTeacherAttendanceExcel($data = false){
		if($data && $this->db->insert('sh_teacher_attendances',$data)){
			return true;
		}
		return false;
	}
	public function deleteTeacherAttendance($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_teacher_attendances')){
			$this->db->delete('sh_teacher_attendance_items',array('attendance_id'=>$id));
			return true;
		}
		return false;
	}
	
	public function finalizeTeacherAttendance($id = false, $data = false, $items = false)
    {
        if ($this->db->update('sh_teacher_attendances', $data, array('id' => $id))) {
            if($items){
				$this->db->insert_batch('sh_teacher_attendance_items',$items);
			}
            return TRUE;
        }
        return FALSE;
    }
	
	public function getTeacherAttendanceItems($id = false){
		$this->db->select('sh_teacher_attendance_items.*,sh_teachers.code,sh_teachers.lastname,sh_teachers.firstname');
		$this->db->join('sh_teachers','sh_teachers.id = sh_teacher_attendance_items.teacher_id','left');
		$q = $this->db->get_where('sh_teacher_attendance_items',array('attendance_id'=>$id));
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getFailureStudents($biller_id = false, $academic_year = false, $program_id = false){
		if($academic_year){
			$this->db->where("sh_student_yearly.academic_year",$academic_year);
		}else{
			$this->db->where("sh_student_yearly.academic_year",date('Y'));
		}
		if($program_id){
			$this->db->where("sh_student_yearly.program_id",$program_id);
		}
		if($biller_id){
			$this->db->where("sh_student_yearly.biller_id",$biller_id);
		}
		$this->db->where("sh_student_yearly.result","fail");
		$this->db->select("
							sh_students.lastname,
							sh_students.firstname,
							sh_students.lastname_other,
							sh_students.firstname_other,
							sh_students.gender,
							sh_students.dob,
							sh_student_yearly.grade_name,
							sh_student_yearly.program_id,
							sh_student_yearly.rank,
							sh_student_yearly.academic_year,
							sh_student_yearly.student_id,
							sh_student_yearly.grade_id,
							sh_student_yearly.class_id,
							sh_student_yearly.class_name,
							sh_student_yearly.average
						")
				->join("sh_students","sh_students.id = sh_student_yearly.student_id","left")
				->group_by("sh_student_yearly.program_id,sh_student_yearly.student_id");
		$q = $this->db->get("sh_student_yearly");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function bestStudentByStudyYear($biller_id = false, $academic_year = false, $program_id = false){
		if($academic_year){
			$this->db->where("sh_student_yearly.academic_year",$academic_year);
		}else{
			$this->db->where("sh_student_yearly.academic_year",date('Y'));
		}
		if($program_id){
			$this->db->where("sh_student_yearly.program_id",$program_id);
		}
		if($biller_id){
			$this->db->where("sh_student_yearly.biller_id",$biller_id);
		}
		$this->db->select("
							sh_students.lastname,
							sh_students.firstname,
							sh_students.lastname_other,
							sh_students.firstname_other,
							sh_students.gender,
							sh_students.dob,
							sh_student_yearly.grade_name,
							sh_student_yearly.program_id,
							sh_student_yearly.rank,
							sh_student_yearly.academic_year,
							sh_student_yearly.student_id,
							sh_student_yearly.grade_id,
							sh_student_yearly.class_id,
							top_averate.average
						")
				->join("(SELECT
							program_id,
							grade_id,
							max( average ) AS average 
						FROM
							".$this->db->dbprefix('sh_student_yearly')." 
						GROUP BY
							program_id,
							grade_id) as top_averate","top_averate.program_id = sh_student_yearly.program_id AND top_averate.grade_id = sh_student_yearly.grade_id AND top_averate.average = sh_student_yearly.average","inner")
				->join("sh_students","sh_students.id = sh_student_yearly.student_id","left")
				->group_by("sh_student_yearly.program_id,sh_student_yearly.grade_id");
		$q = $this->db->get("sh_student_yearly");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSectionResultByStudntID($student_id = false, $academic_year = false, $grade_id = false, $class_id = false){
		if($student_id){
			$this->db->where("student_id",$student_id);
		}
		if($academic_year){
			$this->db->where("academic_year",$academic_year);
		}
		if($grade_id){
			$this->db->where("grade_id",$grade_id);
		}
		if($class_id){
			$this->db->where("class_id",$class_id);
		}
		$this->db->order_by("sh_student_sectionly.section_name");
		$q = $this->db->get("sh_student_sectionly");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
		
	}
	
	public function getChartData(){
		$this->db->select("sh_study_infos.academic_year,
							count(".$this->db->dbprefix('sh_study_infos').".id ) AS total_student,
							IF(total_average > 0, total_average / count(".$this->db->dbprefix('sh_study_infos').".id ),0) as total_average,
							IFNULL(total_failure,0) as total_failure
						")
					->join("(SELECT
								`academic_year`,
								sum( average ) AS total_average,
								sum(IF( result = 'fail', 1, 0 )) AS total_failure 
							FROM
								".$this->db->dbprefix('sh_student_yearly')."
							GROUP BY
								`academic_year`) as result","result.academic_year = sh_study_infos.academic_year","LEFT")	
					->group_by("sh_study_infos.academic_year")
					->order_by("sh_study_infos.academic_year","desc");
		$q = $this->db->get("sh_study_infos");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getFeedbackGroupQuestions($sort = false){
		if($sort){
			$this->db->order_by("sh_feedback_question_groups.id",$sort);
		}
		$q = $this->db->get("sh_feedback_question_groups");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getFeedBackQuestionByID($id = false){
		$this->db->select("sh_feedback_questions.*,sh_feedback_question_groups.name as group_name");
		$this->db->join("sh_feedback_question_groups","sh_feedback_question_groups.id = sh_feedback_questions.group_id","inner");
		$q = $this->db->get_where("sh_feedback_questions",array("sh_feedback_questions.id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function addFeedbackQuestion($data = false){
		if($data && $this->db->insert("sh_feedback_questions",$data)){
			return true;
		}
		return false;
	}
	public function updateFeedbackQuestion($id = false, $data = false){
		if($id && $this->db->update("sh_feedback_questions",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function deleteFeedbackQuestion($id = false){
		if($id && $this->db->delete("sh_feedback_questions",array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	public function getGradeFeeByID($id = false){
		$q = $this->db->get_where("sh_grade_fees", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addGradeFee($data = false){
		if($this->db->insert("sh_grade_fees",$data)){
			return true;
		}
		return false;
	}
	
	public function updateGradeFee($id = false, $data = false){
		if($id && $data && $this->db->update('sh_grade_fees',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteGradeFee($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_grade_fees')){
			return true;
		}
		return false;
	}
	public function getStudentGradeFees($fee_type = false, $grade_id = false, $child_no = false, $student_type = false){
		if($fee_type){
			$this->db->where("sh_grade_fees.fee_type",$fee_type);
		}
		if($grade_id){
			$this->db->where("sh_grade_fees.grade_id",$grade_id);
		}
		if($child_no){
			$this->db->where_in("IFNULL(".$this->db->dbprefix('sh_grade_fees').".child_no,0)",array(0,$child_no));
		}
		if($student_type){
			$this->db->where_in("sh_grade_fees.student_type",array("all",$student_type));
		}
		$this->db->select("sh_grade_fees.*,
							products.code as product_code,
							products.name as product_name,
							products.type as product_type,
							products.price as product_price,
						");
		$this->db->join("products","products.id = sh_grade_fees.product_id","inner");
		$q = $this->db->get("sh_grade_fees");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getSiblings($family_id = false){
		$this->db->where("sh_students.status !=", "inactive");
		$this->db->select("sh_students.*");
		$this->db->order_by("sh_students.child_no,sh_students.order_child_no");
		$this->db->join("sh_students","sh_students.id = sh_family_groups.student_id","inner");
		$q = $this->db->get_where('sh_family_groups', array('sh_family_groups.family_id' => $family_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getEmergencyByFamily($family_id = false){
		$this->db->where("sh_student_families.type","emergency");
		$q = $this->db->get_where('sh_student_families', array('sh_student_families.family_id' => $family_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getChildNo($student_id = false, $family_id = false){
		if(!$family_id){
			$student = $this->getStudentByID($student_id);
			$family_id = isset($student->family_id) ? $student->family_id : '';
		}
		if($student_id && $family_id){
			$this->db->select("count(id) as child_no");
			$this->db->where("family_id",$family_id);
			$this->db->where("student_id <=",$student_id);
			$q = $this->db->get("sh_family_groups");
			if ($q->num_rows() > 0) {
				return $q->row()->child_no;
			}
		}else{
			return 1;
		}
	}
	public function getCustomerByStudent($student_id = false)
	{
		$q = $this->db->get_where("companies", array('student_id' => $student_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function autoInvoice($study_id = false){
		$study_info = $this->getStudentInfoByID($study_id);
		if($study_info && $study_info->auto_invoice == 1){
			$sale_id = $study_info->sale_id;
			$tuition_sale_id = $study_info->tuition_sale_id;
			$child_no = $study_info->child_no;
			if($study_info->issue_invoice == "yes"){
				if($study_info->type){
					$student_type = $study_info->type;
				}else{
					$study_infos = $this->getStudyInfoByStudent($study_info->student_id);
					if(count($study_infos) > 1){
						$student_type = "old";
					}else{
						$student_type = "new";
					}
				}
				$sale_data["type"] = $student_type;
				$fee_types = array("Enrollment", "Tuition");
				foreach($fee_types as $fee_type){
					$grade_fees = $this->getStudentGradeFees($fee_type,$study_info->grade_id,$child_no,$student_type);
					$sale = false;
					$sale_items = false;
					if($grade_fees){
						$customer = $this->getCustomerByStudent($study_info->student_id);
						$total_items = 0;
						$total = 0;
						foreach($grade_fees as $grade_fee){
							$total_items++;
							$total += $grade_fee->product_price;
							$sale_items[] = array(
								'product_id' => $grade_fee->product_id,
								'product_code' => $grade_fee->product_code,
								'product_name' => $grade_fee->product_name,
								'product_type' => $grade_fee->product_type,
								'net_unit_price' => $grade_fee->product_price,
								'unit_price' => $grade_fee->product_price,
								'real_unit_price' => $grade_fee->product_price,
								'quantity' => 1,
								'unit_quantity' => 1,
								'warehouse_id' => $this->Settings->default_warehouse,
								'subtotal' =>  $grade_fee->product_price,
								'comment' => $grade_fee->name,
							);
						}
						$payment_term_info = $this->site->getPaymentTermsByID($this->Settings->default_payment_term);
						if($payment_term_info){
							if($payment_term_info->term_type=='end_month'){
								$due_date = date("Y-m-t", strtotime($study_info->date));
							}else{
								$due_date =  date('Y-m-d', strtotime('+' . $payment_term_info->due_day . ' days', strtotime($study_info->date)));
							}
						}else{
							$due_date = null;
						}
						$biller_details = $this->site->getCompanyByID($study_info->biller_id);
						$sale = array(
							'date' => $study_info->date,
							'customer_id' => $customer->id,
							'customer' => $customer->company,
							'biller_id' => $study_info->biller_id,
							'biller' => $biller_details->company,
							'warehouse_id' => $this->Settings->default_warehouse,
							'total_items' => $total_items,
							'total' => $total,
							'sale_status' => "completed",
							'payment_term' => $this->Settings->default_payment_term,
							'due_date' => $due_date,
							'delivery_status' => 'completed',
							'type' => 'school',
							'academic_year' => $study_info->academic_year,
							'program_id' => $study_info->program_id,
							'grade_id' => $study_info->grade_id,
							'child_no' => $child_no,
							'fee_type' => $fee_type,
						);
						
						if($sale && $sale_items){
							if($fee_type == "Enrollment"){
								$tmp_sale_id = $sale_id;
							}else if($fee_type == "Tuition"){
								$tmp_sale_id = $tuition_sale_id;
							}
							if($tmp_sale_id > 0 && $old_sale = $this->getSaleByID($tmp_sale_id)){
								$order_discount = 0;
								$order_tax = 0;
								if($old_sale->order_discount_id){
									$opos = strpos($old_sale->order_discount_id, "%");
									if ($opos !== false) {
										$ods = explode("%", $old_sale->order_discount_id);
										$order_discount = (($total) * (Float) ($ods[0])) / 100;
									} else {
										$order_discount = $old_sale->order_discount_id;
									}
								}
								if ($old_sale->order_tax_id && $order_tax_details = $this->site->getTaxRateByID($old_sale->order_tax_id)) {
									if ($order_tax_details->type == 2) {
										$order_tax = $order_tax_details->rate;
									}
									if ($order_tax_details->type == 1) {
										$order_tax = ((($total) - $order_discount) * $order_tax_details->rate) / 100;
									}
								}
								$grand_total = $total + $order_tax - $order_discount;
								$sale["order_discount"] = $order_discount;
								$sale["total_discount"] = $order_discount;
								$sale["order_tax"] = $order_tax;
								$sale["grand_total"] = $grand_total;
								$sale["updated_by"] = $this->session->userdata('user_id');
								$sale["updated_at"] = date('Y-m-d H:i:s');
								if(!$old_sale->installment && !$old_sale->return_id){
									if($this->db->update("sales",$sale,array("id"=>$tmp_sale_id))){
										$this->db->delete("sale_items",array("sale_id"=>$tmp_sale_id));
										foreach($sale_items as $sale_item){
											$sale_item["sale_id"] = $tmp_sale_id;
											$this->db->insert("sale_items",$sale_item);
										}
										$this->site->syncSalePayments($tmp_sale_id);
									}
								}else if($this->bpas->formatDecimal($grand_total) != $this->bpas->formatDecimal($old_sale->grand_total)){
									$this->db->update("sales",array("update"=>1),array("id"=>$tmp_sale_id));
								}
							}else{
								$reference_no =  $this->site->getReference('so',$study_info->biller_id);
								$grand_total = $total;
								$sale["reference_no"] = $reference_no;
								$sale["payment_status"] = "pending";
								$sale["grand_total"] = $grand_total;
								$sale["paid"] = 0;
								$sale["created_by"] = $this->session->userdata('user_id');
								if($this->db->insert("sales",$sale)){
									$tmp_sale_id = $this->db->insert_id();
									foreach($sale_items as $sale_item){
										$sale_item["sale_id"] = $tmp_sale_id;
										$this->db->insert("sale_items",$sale_item);
									}
								}
							}
							if($fee_type == "Enrollment"){
								$sale_data["sale_id"] = $tmp_sale_id;
							}else if($fee_type == "Tuition"){
								$sale_data["tuition_sale_id"] = $tmp_sale_id;
							}
						}
					}
				}
				if($sale_data){
					$this->db->update("sh_study_infos",$sale_data,array("id"=>$study_id));
				}
			}
		}
	}
	
	public function getStudentStudyInfo($student_id = false, $data = false)
	{
		if($student_id){
			$this->db->where("sh_study_infos.student_id",$student_id);
		}
		if($data) {
			$this->db->where($data);
		}
		$this->db->order_by("sh_study_infos.academic_year");
		$this->db->order_by("sh_study_infos.grade_id");
		$this->db->order_by("sh_study_infos.id");
		$q = $this->db->get("sh_study_infos");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	
	public function getStudyInfoByStudent($student_id = false){
		$this->db->where("biller_id >",0);
		$this->db->where("student_id",$student_id);
		$q = $this->db->get("sh_study_infos");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getPaymentsBySale($sale_id = false)
    {
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	public function getSaleReturns($sale_id = false)
    {
        $q = $this->db->get_where('sales', array('sale_id' => $sale_id,'sale_status' => 'returned'));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
	
	public function getSaleByID($id = false)
	{
		$q = $this->db->get_where("sales", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSaleItemsByID($sale_id = false){
		$this->db->select('sale_items.*,products.cf1');
		$this->db->join('products','products.id = sale_items.product_id','left');
		$q = $this->db->get_where('sale_items', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function deleteSale($id = false)
	{
		if ($id && $this->db->delete('sales', array('id' => $id))) {
			$payments = $this->getPaymentsBySale($id);
			$sales_returns = $this->getSaleReturns($id);
			$this->db->delete('sales', array('sale_id' => $id));
			$this->db->delete('payments', array('sale_id' => $id));
			$this->db->delete('sale_items', array('sale_id' => $id));
			if($payments){
				foreach($payments as $payment){
					if ($payment->paid_by == 'gift_card') {
						$gc = $this->site->getGiftCardByNO($payment->cc_no);
						$this->db->update('gift_cards', array('balance' => ($gc->balance+$payment->amount)), array('card_no' => $payment->cc_no));
					} elseif ($payment->paid_by == 'deposit') {
						$this->site->sysnceCustomerDeposit($sale->customer_id);
					}
				}
			}
			if($sales_returns){
				foreach($sales_returns as $sales_return){
					$this->db->delete('payments', array('sale_id' => $sales_return->id));
				}
			}
			return true;
		}
		return false;
	}
	
	public function getStudyInfoByID($id = false)
	{
		$q = $this->db->get_where("sh_study_infos", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function getLastStudyInfo($student_id = false, $academic_year = false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year", $academic_year);
		}
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->select("sh_study_infos.*, companies.name as biller_name, companies.company as biller_company,sh_classes.name as class_name");
		$this->db->order_by("sh_study_infos.academic_year","desc");
		$this->db->order_by("sh_study_infos.id","desc");
		$this->db->join("companies","companies.id = sh_study_infos.biller_id","inner");
		$this->db->join("sh_classes","sh_classes.id = sh_study_infos.class_id","left");
		$q = $this->db->get_where("sh_study_infos", array('sh_study_infos.student_id' => $student_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getFamilyByRelationship($family_id = false, $relationship = false, $type = "family"){
		if($type){
			$this->db->where("sh_student_families.type",$type);
		}
		$this->db->where("relationship",$relationship);
		$q = $this->db->get_where("sh_student_families", array('family_id' => $family_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getFamiliesByRelationship($family_id = false, $relationship = false, $type = "family"){
		if($type){
			$this->db->where("sh_student_families.type",$type);
		}
		$this->db->where("relationship",$relationship);
		$q = $this->db->get_where("sh_student_families", array('family_id' => $family_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getContactApp($family_id = false, $type = "family"){
		if($type){
			$this->db->where("sh_student_families.type",$type);
		}
		$this->db->where("school_app","yes");
		$q = $this->db->get_where("sh_student_families", array('family_id' => $family_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getFamilyBanks($family_id = false){
		$q = $this->db->get_where("sh_student_banks", array('family_id' => $family_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getEmergencyByID($id = false)
	{
		$q = $this->db->get_where('sh_student_families', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addEmergency($student_id = false, $data =array())
	{
		if($data && $this->db->insert('sh_student_families',$data)){
			return true;
		}
		return false;
	}
	
	public function updateEmergency($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_student_families', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteEmergency($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_student_families')){
			return true;
		}
		return false;
	}
	
	public function getBankByID($id = false)
	{
		$q = $this->db->get_where('sh_student_banks', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addBank($student_id = false, $data =array())
	{
		if($data && $this->db->insert('sh_student_banks',$data)){
			return true;
		}
		return false;
	}
	
	public function updateBank($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_student_banks', $data)){
			return true;
		}
		return false;
	}
	
	public function deleteBank($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_student_banks')){
			return true;
		}
		return false;
	}
	public function getAcademicYears(){
		$this->db->select("academic_year");
		$this->db->group_by("academic_year");
		$this->db->order_by("academic_year");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
			$year = Date('Y');
            foreach (($q->result()) as $key => $row) {
                $data[] = $row;
                $year   = $row->academic_year;
            }
            for ($i=1; $i <= 5; $i++) { 
            	$data[] = (object) ['academic_year' => ($year + $i)];
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTestingAcademicYears(){
		$this->db->select("academic_year");
		$this->db->group_by("academic_year");
		$this->db->order_by("academic_year");
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	// public function addSale($data = false, $products = false, $study_info = false)
	// {
	// 	if (!empty($study_info)) {
	// 		if ($student_study_info = $this->getStudentStudyInfo(null, $study_info)) {
	// 			$data['study_info_id'] = $student_study_info[0]->id;
	// 		}
	// 	}
	// 	if($this->db->insert("sales", $data)){
	// 		$sale_id = $this->db->insert_id();
	// 		if($products){
	// 			$sale_items = false;
	// 			foreach($products as $product){
	// 				$product['sale_id'] = $sale_id;
	// 				$sale_items[]       = $product;
	// 			}
	// 			if($sale_items){
	// 				$this->db->insert_batch("sale_items", $sale_items);
	// 			}
	// 		}
	// 		return true;
	// 	}
	// 	return false;
	// }

	public function addSale($data = [], $items = [], $study_info = false, $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null)
    {  
        if (empty($si_return)) {
            $cost = $this->site->costing($items);
        }
        $this->db->trans_start();
		if (!empty($study_info)) {
			if ($student_study_info = $this->getStudentStudyInfo(null, $study_info)) {
				$data['study_info_id'] = $student_study_info[0]->id;
			}
		}
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();
            //=========Add Accounting =========//
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ((isset($payment['amount']) && $payment['amount'] == '')) {
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id'] = $sale_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            } elseif ($this->site->getReference('st') == $data['reference_no']) {
                $this->site->updateReference('st');
            }
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                if ($this->Settings->product_option && isset($item['max_serial'])) {
                    $this->db->update('product_options', ['start_no' => $item['serial_no'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                }
                $aprroved['purchase_request_id'] = $sale_id;
                $this->db->insert('approved', $aprroved);
                if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment' && empty($si_return)) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $sale_id;
                            $item_cost['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $sale_id;
                                $ic['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }
            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                $this->site->syncPurchaseItems($cost);
            }
            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo' && $product->module_type != "property") {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->UpdateCostingAndPurchaseItem($return_item, $combo_item->id, ($return_item['quantity'] * $combo_item->qty));
                        }
                    } else{
                        $this->UpdateCostingAndPurchaseItem($return_item, $return_item['product_id'], $return_item['quantity'],$return_item['expiry']);
                    }
                }
                $q=$this->db->get_where('sales', ['id' => $data['sale_id']],1);
                if ($q->num_rows() > 0) {
                    $return_sale_total_ = ($q->row()->return_sale_total ? $q->row()->return_sale_total : 0);
                }
                
                $this->db->update('sales', ['return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => ($data['grand_total'] + $return_sale_total_), 'return_id' => $sale_id], ['id' => $data['sale_id']]);
                $customer = $this->site->getCompanyByID($data['customer_id']);
                if($customer->save_point){
                    if (!empty($this->Settings->each_spent)) {
                        $points       = floor(((-1 * $data['grand_total']) / $this->Settings->each_spent) * $this->Settings->ca_point);
                        $total_points = $customer->award_points - $points;
                        $this->db->update('companies', ['award_points' => $total_points], ['id' => $data['customer_id']]);
                    }
                }
                if(isset($data['saleman_by'])){
                    $staff = $this->site->getUser($data['saleman_by']);
                }
                if($staff->save_point){
                    if (!empty($this->Settings->each_sale)) {
                        $points       = floor(((-1 * $data['grand_total']) / $this->Settings->each_sale) * $this->Settings->sa_point);
                        $total_points = $staff->award_points - $points;
                        $this->db->update('users', ['award_points' => $total_points], ['id' => $data['saleman_by']]);
                    }
                }
            }
            if(isset($data['saleman_by'])){
                $staff = $this->site->getUser($data['saleman_by']);
            }
            if($commission_product){
                $commission        = $commission_product;
                $total_commissions = $staff->commission_product + $commission;
                $this->db->update('users', ['commission_product' => $total_commissions], ['id' => $data['saleman_by']]);
            }
            if ($data['payment_status'] == 'paid') {
                $this->site->update_property_status($sale_id,'sold');
            }
            if ($data['payment_status'] == 'booking' || $data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', ['balance' => $payment['gc_balance']], ['card_no' => $payment['cc_no']]);
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update(
                            'companies', 
                            [
                                'deposit_amount' => ($customer->deposit_amount - $payment['amount']),
                                'deposit_amount_usd' => ($customer->deposit_amount_usd - $payment['amount_usd']),
                                'deposit_amount_khr' => ($customer->deposit_amount_khr - $payment['amount_khr']),
                                'deposit_amount_thb' => ($customer->deposit_amount_thb - $payment['amount_thb']),
                            ], 
                            ['id' => $customer->id]);
                    }
                    $this->db->insert('payments', $payment);
                }
                //=========Add Accounting =========//
                $payment_id = $this->db->insert_id();
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id']= $payment_id;
                        if (empty($accTranPayment['reference_no'])) {
                            $accTranPayment['reference_no'] = $payment['reference_no'];
                        }
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
                 //=========End Accounting =========//
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                if ($this->site->getReference('pp') == $payment['reference_no']) {
                    $this->site->updateReference('pp');
                }
                $this->site->syncSalePayments($sale_id);
            }
            $this->site->syncQuantity($sale_id,null,null,null, $payment_status);
            $customer = $this->site->getCompanyByID($data['customer_id']);
            if($customer->save_point){
                $this->bpas->update_award_points($data['grand_total'], $data['customer_id'], null);
            }
            if (isset($data['saleman_by']) && !empty($data['saleman_by'])) {
            	$staff = $this->site->getUser($data['saleman_by']);
	            if(isset($staff->save_point) && $staff->save_point){
	                $this->bpas->update_award_points($data['grand_total'], null, $data['saleman_by']);
	            }
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }
        return false;
    }

	public function updateSale($id = false, $data = false, $products = false, $study_info = false)
	{
		if (!empty($study_info)) {
			if ($student_study_info = $this->getStudentStudyInfo(null, $study_info)) {
				$data['study_info_id'] = $student_study_info[0]->id;
			}
		}
        if ($this->db->update('sales', $data, array('id' => $id)) && $this->db->delete('sale_items', array('sale_id' => $id))) {
			if($products){
				$this->db->insert_batch('sale_items', $products);
			}
            $this->site->syncSalePayments($id);
            return true;
        }
        return false;
    }
	
	public function addSaleDiscount($id = false, $data = false){
		if ($this->db->update('sales', $data, array('id' => $id))){
            $this->site->syncSalePayments($id);
            return true;
        }
        return false;
	}
	
	
	public function getNumberStudent($biller = false,$program = false,$grade = false){
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		$this->db->where("(".$this->db->dbprefix('sales').".id IS NULL OR IFNULL(".$this->db->dbprefix('sales').".payment_status,'') != 'pending')");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->join("sales","sales.id = sh_study_infos.sale_id","left");
		$this->db->group_by("sh_study_infos.academic_year");
		$this->db->group_by("sh_study_infos.biller_id");
		$this->db->group_by("sh_study_infos.type");
		$this->db->select("count(".$this->db->dbprefix('sh_study_infos').".id) as total_student,sh_study_infos.academic_year,sh_study_infos.biller_id,sh_study_infos.type");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->academic_year][$row->biller_id][$row->type] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getEnrollmentByGrade($academic_year= false, $biller = false,$program = false,$grade = false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		$this->db->where("(".$this->db->dbprefix('sales').".id IS NULL OR IFNULL(".$this->db->dbprefix('sales').".payment_status,'') != 'pending')");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->join("sales","sales.id = sh_study_infos.sale_id","left");
		$this->db->join("sh_students","sh_students.id = sh_study_infos.student_id","inner");
		$this->db->group_by("sh_study_infos.grade_id");
		$this->db->group_by("sh_study_infos.biller_id");
		$this->db->group_by("sh_students.gender");
		$this->db->group_by("sh_study_infos.type");
		$this->db->select("count(".$this->db->dbprefix('sh_study_infos').".id) as total_student,sh_study_infos.grade_id,sh_study_infos.biller_id,sh_students.gender,sh_study_infos.type");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->grade_id][$row->biller_id][$row->gender][$row->type] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getEnrollmentGradeByAcademic($biller = false,$program = false,$grade = false){
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		$this->db->where("(".$this->db->dbprefix('sales').".id IS NULL OR IFNULL(".$this->db->dbprefix('sales').".payment_status,'') != 'pending')");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->join("sales","sales.id = sh_study_infos.sale_id","left");
		$this->db->join("sh_students","sh_students.id = sh_study_infos.student_id","inner");
		$this->db->group_by("sh_study_infos.academic_year");
		$this->db->group_by("sh_study_infos.grade_id");
		$this->db->group_by("sh_study_infos.type");
		$this->db->select("count(".$this->db->dbprefix('sh_study_infos').".id) as total_student,sh_study_infos.grade_id,sh_study_infos.type,sh_study_infos.academic_year");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->academic_year][$row->grade_id][$row->type] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	
	public function getMonthEnrollment($academic_year= false, $biller = false,$program = false,$grade = false,$type = false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		if($type){
			$this->db->where("sh_study_infos.type",$type);
		}
		$this->db->select("
							count(".$this->db->dbprefix('sh_study_infos').".id) as total_enroll,
							sh_study_infos.biller_id,
							MONTH(IFNULL(payments.date,".$this->db->dbprefix('sh_study_infos').".date)) as month_enroll
						");
		$this->db->join("sales","sales.id = sh_study_infos.sale_id","left");
		$this->db->join("(SELECT sale_id, min(date) as date FROM ".$this->db->dbprefix('payments')." GROUP BY sale_id) as payments","payments.sale_id = sales.id","left");
		$this->db->where("(".$this->db->dbprefix('sales').".id IS NULL OR payments.sale_id > 0)");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->group_by("sh_study_infos.biller_id, MONTH(IFNULL(payments.date,".$this->db->dbprefix('sh_study_infos').".date))");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->month_enroll] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getYearEnrollment($academic_year= false, $biller = false,$program = false,$grade = false,$type = false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		if($type){
			$this->db->where("sh_study_infos.type",$type);
		}
		$this->db->select("
							count(".$this->db->dbprefix('sh_study_infos').".id) as total_enroll,
							sh_study_infos.biller_id,
							IFNULL(".$this->db->dbprefix('sales').".academic_year,".$this->db->dbprefix('sh_study_infos').".academic_year) as year_enroll
						");
		$this->db->join("sales","sales.id = sh_study_infos.sale_id","left");
		$this->db->where("(".$this->db->dbprefix('sales').".id IS NULL OR IFNULL(".$this->db->dbprefix('sales').".payment_status,'') != 'pending')");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->group_by("sh_study_infos.biller_id, IFNULL(".$this->db->dbprefix('sales').".academic_year,".$this->db->dbprefix('sh_study_infos').".academic_year)");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->year_enroll] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getAllEnrollment($academic_year= false, $biller = false,$program = false,$grade = false,$type = false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		if($type){
			$this->db->where("sh_study_infos.type",$type);
		}
		$this->db->select("
							count(".$this->db->dbprefix('sh_study_infos').".id) as total_enroll,
							sh_study_infos.biller_id,
							sh_study_infos.academic_year
						");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->group_by("sh_study_infos.biller_id, sh_study_infos.academic_year");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->academic_year] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getTuitionFee_05_10_2022($academic_year= false, $biller = false,$program = false,$grade = false,$class = false)
	{
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id",$class);
		}
		$this->db->select("
							sh_study_infos.biller_id,
							count(".$this->db->dbprefix('sales').".id) as total_qty,
							SUM(IF(".$this->db->dbprefix('sales').".order_discount > 0,1,0)) as discount_qty
						");
		
		$this->db->join("sales","sales.id = sh_study_infos.tuition_sale_id","inner");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->group_by("sh_study_infos.biller_id");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getTuitionFee($academic_year= false, $biller = false, $program = false, $grade = false, $class = false)
	{
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year", $academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id", $biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id", $program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id", $grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id", $class);
		}
		$this->db->select("
				sh_study_infos.biller_id,
				SUM(COALESCE(tuition_sales.tuition_qty, 0)) as total_qty,
				SUM(IF(COALESCE(tuition_sales.order_discount, 0) > 0, 1, 0)) as discount_qty
			");
		$this->db->join(
			" 
				(
					SELECT 
						{$this->db->dbprefix('sales')}.id AS id,
						{$this->db->dbprefix('sales')}.biller_id,
						{$this->db->dbprefix('sales')}.study_info_id,
						{$this->db->dbprefix('sales')}.order_discount,
						SUM({$this->db->dbprefix('sale_items')}.quantity) AS tuition_qty
					FROM {$this->db->dbprefix('sales')}
					LEFT JOIN {$this->db->dbprefix('sale_items')} ON {$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id
					LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id = {$this->db->dbprefix('sale_items')}.product_id
					LEFT JOIN {$this->db->dbprefix('categories')} ON {$this->db->dbprefix('categories')}.id = {$this->db->dbprefix('products')}.category_id
					WHERE {$this->db->dbprefix('sales')}.study_info_id IS NOT NULL AND LOWER({$this->db->dbprefix('categories')}.code) = 'tuition'
					GROUP BY {$this->db->dbprefix('sales')}.id 
				) AS tuition_sales
			", "tuition_sales.study_info_id = sh_study_infos.id", "inner"
		);
		$this->db->where("sh_study_infos.biller_id >", 0);
		$this->db->group_by("sh_study_infos.biller_id");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTuitionInstallment_05_10_2022($academic_year= false, $biller = false,$program = false,$grade = false,$class = false)
	{
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id",$class);
		}
		
		$this->db->select("
							sh_study_infos.biller_id,
							MONTH(".$this->db->dbprefix('installments').".created_date) as month,
							COUNT(".$this->db->dbprefix('installments').".id) as qty
						");
		
		$this->db->join("sales","sales.id = sh_study_infos.tuition_sale_id","inner");
		$this->db->join("installments","installments.sale_id = sales.id","inner");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->group_by("sh_study_infos.biller_id,MONTH(".$this->db->dbprefix('installments').".created_date)");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->month] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getTuitionInstallment($academic_year = false, $biller = false, $program = false, $grade = false, $class = false)
	{
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year", $academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id", $biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id", $program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id", $grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id", $class);
		}
		$this->db->select("
				sh_study_infos.biller_id,
				MONTH(tuition_sales.date) as month,
				SUM(COALESCE(tuition_sales.tuition_installment_qty, 0)) AS qty
			");

		$this->db->join(
			" 
				(
					SELECT 
						{$this->db->dbprefix('sales')}.id AS id,
						{$this->db->dbprefix('sales')}.date,
						{$this->db->dbprefix('sales')}.biller_id,
						{$this->db->dbprefix('sales')}.study_info_id,
						{$this->db->dbprefix('sales')}.order_discount,
						SUM({$this->db->dbprefix('sale_items')}.quantity) AS tuition_qty,
						SUM(
							IF(
								{$this->db->dbprefix('sale_items')}.product_unit_id != {$this->db->dbprefix('products')}.unit, 
								{$this->db->dbprefix('sale_items')}.quantity, 0
							)
						) AS tuition_installment_qty
					FROM {$this->db->dbprefix('sales')}
					LEFT JOIN {$this->db->dbprefix('sale_items')} ON {$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id
					LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id = {$this->db->dbprefix('sale_items')}.product_id
					LEFT JOIN {$this->db->dbprefix('categories')} ON {$this->db->dbprefix('categories')}.id = {$this->db->dbprefix('products')}.category_id
					WHERE {$this->db->dbprefix('sales')}.study_info_id IS NOT NULL AND LOWER({$this->db->dbprefix('categories')}.code) = 'tuition'
					GROUP BY {$this->db->dbprefix('sales')}.id 
				) AS tuition_sales
			", "tuition_sales.study_info_id = sh_study_infos.id", "inner"
		);
		$this->db->where("sh_study_infos.biller_id >", 0);
		$this->db->group_by("sh_study_infos.biller_id, YEAR(tuition_sales.date), MONTH(tuition_sales.date)");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->month] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTuitionFull_05_10_2022($academic_year= false, $biller = false,$program = false,$grade = false,$class = false)
	{
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id",$class);
		}
		$this->db->select("
							sh_study_infos.biller_id,
							MONTH(payments.date) as month,
							COUNT(".$this->db->dbprefix('sales').".id) as qty
						");
		
		$this->db->join("sales","sales.id = sh_study_infos.tuition_sale_id","inner");
		$this->db->join("(SELECT sale_id, max(date) as date FROM ".$this->db->dbprefix('payments')." GROUP BY sale_id) as payments","payments.sale_id = sales.id","left");
		$this->db->join("installments","installments.sale_id = sales.id","left");
		$this->db->where("installments.id IS NULL");
		$this->db->where("sales.payment_status","paid");
		$this->db->where("sh_study_infos.biller_id >",0);
		$this->db->group_by("sh_study_infos.biller_id,MONTH(payments.date)");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->month] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getTuitionFull($academic_year = false, $biller = false, $program = false, $grade = false, $class = false)
	{
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year", $academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id", $biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id", $program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id", $grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id", $class);
		}
		$this->db->select("
				sh_study_infos.biller_id,
				MONTH(tuition_sales.date) as month,
				SUM(COALESCE(tuition_sales.tuition_full_qty, 0)) AS qty
			");
		
		$this->db->join(
			" 
				(
					SELECT 
						{$this->db->dbprefix('sales')}.id AS id,
						{$this->db->dbprefix('sales')}.date,
						{$this->db->dbprefix('sales')}.biller_id,
						{$this->db->dbprefix('sales')}.study_info_id,
						{$this->db->dbprefix('sales')}.order_discount,
						SUM({$this->db->dbprefix('sale_items')}.quantity) AS tuition_qty,
						SUM(
							IF(
								{$this->db->dbprefix('sale_items')}.product_unit_id = {$this->db->dbprefix('products')}.unit, 
								{$this->db->dbprefix('sale_items')}.quantity, 0
							)
						) AS tuition_full_qty
					FROM {$this->db->dbprefix('sales')}
					LEFT JOIN {$this->db->dbprefix('sale_items')} ON {$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id
					LEFT JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id = {$this->db->dbprefix('sale_items')}.product_id
					LEFT JOIN {$this->db->dbprefix('categories')} ON {$this->db->dbprefix('categories')}.id = {$this->db->dbprefix('products')}.category_id
					WHERE {$this->db->dbprefix('sales')}.study_info_id IS NOT NULL AND LOWER({$this->db->dbprefix('categories')}.code) = 'tuition'
					GROUP BY {$this->db->dbprefix('sales')}.id 
				) AS tuition_sales
			", "tuition_sales.study_info_id = sh_study_infos.id", "inner"
		);
		$this->db->where("sh_study_infos.biller_id >", 0);
		$this->db->group_by("sh_study_infos.biller_id, YEAR(tuition_sales.date), MONTH(tuition_sales.date)");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->month] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTotalSale($academic_year= false, $biller = false, $program = false, $grade = false, $class = false, $fee_type = false)
	{
		if($academic_year){
			$this->db->where("sales.academic_year", $academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id", $biller);
		}
		if($program){
			$this->db->where("sales.program_id", $program);
		}
		if($grade){
			$this->db->where("sales.grade_id", $grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id", $class);
		}
		if($fee_type){
			$this->db->where("sales.fee_type", $fee_type);
		}
		$this->db->where("sales.module_type","school");
		$this->db->select("
							sales.biller_id,
							SUM(".$this->db->dbprefix('sales').".total) as total,
							SUM(".$this->db->dbprefix('sales').".order_discount) as order_discount,
							SUM(".$this->db->dbprefix('sales').".grand_total) as grand_total
						");
		$this->db->join("companies","companies.id = sales.customer_id","inner");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = companies.student_id AND sh_study_infos.academic_year = sales.academic_year AND sh_study_infos.program_id = sales.program_id AND sh_study_infos.grade_id = sales.grade_id", "LEFT");
		$this->db->group_by("sales.biller_id");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getMonthlyPayment($academic_year= false, $biller = false, $program = false, $grade = false, $class = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id",$class);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->where("sales.module_type","school");
		$this->db->select("
							sales.biller_id,
							MONTH(".$this->db->dbprefix('payments').".date) as month,
							IFNULL(".$this->db->dbprefix('payments').".installment_id,0) as installment_id,
							SUM(IFNULL(".$this->db->dbprefix('payments').".amount,0) + IFNULL(".$this->db->dbprefix('payments').".discount,0)) as total_paid
						");
		$this->db->join("payments","payments.sale_id = sales.id","inner");
		$this->db->join("companies","companies.id = sales.customer_id","inner");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = companies.student_id AND sh_study_infos.academic_year = sales.academic_year AND sh_study_infos.program_id = sales.program_id AND sh_study_infos.grade_id = sales.grade_id", "LEFT");
		$this->db->group_by("
								sales.biller_id,
								MONTH(".$this->db->dbprefix('payments').".date),
								IFNULL(".$this->db->dbprefix('payments').".installment_id,0)
							");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				if($row->installment_id > 0){
					$type = "inst_paid";
				}else{
					$type = "full_paid";
				}
                $data[$row->biller_id][$row->month][$type] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function installmentTimes(){
		$q = $this->db->query("SELECT
									max( id ) AS id 
								FROM
									( SELECT count( id ) AS id FROM ".$this->db->dbprefix('installment_items')." GROUP BY installment_id ) AS installment_times
							");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getSales($academic_year= false, $biller = false,$program = false,$grade = false, $class = false, $student = false, $fee_type = false)
	{
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id",$class);
		}
		if($student){
			$this->db->where("companies.student_id",$student);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							sales.id,
							sales.biller,
							sales.customer,
							sales.total,
							sales.order_discount,
							sales.grand_total,
							sales.reference_no,
							sales.fee_type,
							sh_programs.name as program,
							sh_grades.name as grade,
							sh_classes.name as class,
							sh_study_infos.type
						");
		$this->db->join("sh_programs","sh_programs.id = sales.program_id","left");				
		$this->db->join("sh_grades","sh_grades.id = sales.grade_id","left");
		$this->db->join("companies","companies.id = sales.customer_id","inner");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = companies.student_id AND sh_study_infos.academic_year = sales.academic_year AND sh_study_infos.program_id = sales.program_id AND sh_study_infos.grade_id = sales.grade_id", "LEFT");
		$this->db->join("sh_classes","sh_classes.id = sh_study_infos.class_id","left");
		$this->db->where("sales.module_type","school");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getSales_($academic_year= false, $biller = false,$program = false,$grade = false, $class = false, $student = false, $fee_type = false)
	{
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id",$class);
		}
		if($student){
			$this->db->where("companies.student_id",$student);
		}

		$si = "( SELECT si.sale_id, si.product_id, GROUP_CONCAT(CONCAT(c.name, '__', si.quantity) SEPARATOR '___') as item_nane 
                FROM {$this->db->dbprefix('sale_items')} si 
                LEFT JOIN {$this->db->dbprefix('products')} p on p.id = si.product_id
                LEFT JOIN {$this->db->dbprefix('categories')} c on c.id = p.category_id
        ";
        if($fee_type){
			$si .= " WHERE c.id = {$fee_type} ";
		}
		$si .= " GROUP BY si.sale_id ) FSI";

		$this->db->select("
				sales.id,
				sales.biller,
				sales.customer,
				sales.total,
				sales.order_discount,
				sales.grand_total,
				sales.reference_no,
				sales.fee_type,
				FSI.item_nane as iqty,
				sh_programs.name as program,
				sh_grades.name as grade,
				sh_classes.name as class,
				sh_study_infos.type
			");
		$this->db->join($si, 'FSI.sale_id = sales.id', 'inner');
		$this->db->join("sh_programs","sh_programs.id = sales.program_id","left");				
		$this->db->join("sh_grades","sh_grades.id = sales.grade_id","left");
		$this->db->join("companies","companies.id = sales.customer_id","inner");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = companies.student_id AND sh_study_infos.id = sales.study_info_id", "LEFT");
		$this->db->join("sh_classes","sh_classes.id = sh_study_infos.class_id","left");
		$this->db->where("sales.module_type","school");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getFullPayments($academic_year= false, $biller = false, $program = false, $grade = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->where("sales.module_type","school");
		$this->db->select("
							sales.id,
							payments.reference_no,
							payments.date,
							SUM(IFNULL(".$this->db->dbprefix('payments').".amount,0) + IFNULL(".$this->db->dbprefix('payments').".discount,0)) as total_paid
						");
		$this->db->join("payments","payments.sale_id = sales.id","inner");
		$this->db->where("IFNULL(".$this->db->dbprefix("payments").".installment_id,0)",0);
		$this->db->group_by("sales.id");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getInstallmentPayments($academic_year= false, $biller = false, $program = false, $grade = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->where("sales.module_type","school");
		$this->db->select("
							sales.id,
							payments.reference_no,
							payments.date,
							SUM(IFNULL(".$this->db->dbprefix('payments').".amount,0) + IFNULL(".$this->db->dbprefix('payments').".discount,0)) as total_paid
						");
		$this->db->join("payments","payments.sale_id = sales.id","inner");
		$this->db->where("payments.installment_id >",0);
		$this->db->group_by("payments.installment_item_id");
		$this->db->order_by("payments.installment_item_id");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id][] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	

	public function getSaleStudents($academic_year= false, $biller = false,$program = false,$grade = false,$class_id = false, $student = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($student){
			$this->db->where("sh_students.id",$student);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		if($class_id){
			$this->db->where("sh_study_infos.class_id",$class_id);
		}
		$this->db->select("
							sales.biller,
							sales.biller_id,
							sales.customer,
							sh_students.number,
							sh_students.dob,
							sh_students.child_no,
							sales.number_customer,
							sh_students.id as student_id,
							sh_study_infos.type
						");
		$this->db->join("companies","companies.id = sales.customer_id","inner");
		$this->db->join("sh_students","sh_students.id = companies.student_id","inner");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = companies.student_id AND sh_study_infos.academic_year = sales.academic_year AND sh_study_infos.program_id = sales.program_id AND sh_study_infos.grade_id = sales.grade_id", "left");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sh_students.id,sales.biller");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexStudentFees($academic_year= false, $biller = false,$program = false,$grade = false,$class_id = false, $student = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($student){
			$this->db->where("companies.student_id",$student);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							companies.student_id,
							sales.biller_id,
							IF(".$this->db->dbprefix("installments").".id > 0,'ITuition',".$this->db->dbprefix("sales").".fee_type) as fee_type,
							SUM(".$this->db->dbprefix("sales").".grand_total) as grand_total,
							SUM(IFNULL(bms_payments.paid + IFNULL(total_return_paid,0),0) + IFNULL(bms_payments.discount,0)) as paid
						");
		$this->db->join('(SELECT
								sale_id,
								SUM(ABS(grand_total)) AS total_return,
								SUM(paid) AS total_return_paid
							FROM
								'.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
							GROUP BY
								sale_id) as bms_return', 'bms_return.sale_id=sales.id', 'left')
					->join('(SELECT
								sale_id,
								IFNULL(SUM(amount),0) AS paid,
								IFNULL(SUM(discount),0) AS discount
							FROM
								'.$this->db->dbprefix('payments').'
								
							GROUP BY
								sale_id) as bms_payments', 'bms_payments.sale_id=sales.id', 'left');
		$this->db->join("companies","companies.id = sales.customer_id","inner");
		$this->db->join("installments","installments.sale_id = sales.id and sales.fee_type = 'Tuition'","left");
		if($class_id){
			$this->db->join("sh_study_infos","sh_study_infos.student_id = companies.student_id AND sh_study_infos.academic_year = sales.academic_year AND sh_study_infos.program_id = sales.program_id AND sh_study_infos.grade_id = sales.grade_id", "INNER");
			$this->db->where("sh_study_infos.class_id",$class_id);
		}
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,companies.student_id,IF(".$this->db->dbprefix("installments").".id > 0,'ITuition',".$this->db->dbprefix("sales").".fee_type)");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->student_id][$row->fee_type] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSaleGrades($academic_year= false, $biller = false,$program = false,$grade = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							sales.biller,
							sales.biller_id,
							sales.grade_id,
							sh_grades.name as grade_name
						");
		$this->db->join("sh_grades","sh_grades.id = sales.grade_id","left");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.grade_id,sales.biller");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getIndexGradeFees($academic_year= false, $biller = false,$program = false,$grade = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							sales.biller_id,
							sales.grade_id,
							IF(".$this->db->dbprefix("installments").".id > 0,'ITuition',".$this->db->dbprefix("sales").".fee_type) as fee_type,
							SUM(".$this->db->dbprefix("sales").".grand_total) as grand_total,
							SUM(IFNULL(bms_payments.paid + IFNULL(total_return_paid,0),0) + IFNULL(bms_payments.discount,0)) as paid
						");
		$this->db->join("installments","installments.sale_id = sales.id and sales.fee_type = 'Tuition'","left");
		$this->db->join('(SELECT
								sale_id,
								SUM(ABS(grand_total)) AS total_return,
								SUM(paid) AS total_return_paid
							FROM
								'.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
							GROUP BY
								sale_id) as bms_return', 'bms_return.sale_id=sales.id', 'left')
					->join('(SELECT
								sale_id,
								IFNULL(SUM(amount),0) AS paid,
								IFNULL(SUM(discount),0) AS discount
							FROM
								'.$this->db->dbprefix('payments').'
								
							GROUP BY
								sale_id) as bms_payments', 'bms_payments.sale_id=sales.id', 'left');
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,sales.grade_id,IF(".$this->db->dbprefix("installments").".id > 0,'ITuition',".$this->db->dbprefix("sales").".fee_type)");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->grade_id][$row->fee_type] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexBranchFees($academic_year= false, $biller = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							sales.biller_id,
							IF(".$this->db->dbprefix("installments").".id > 0,'ITuition',".$this->db->dbprefix("sales").".fee_type) as fee_type,
							SUM(".$this->db->dbprefix("sales").".grand_total) as grand_total,
							SUM(IFNULL(bms_payments.paid + IFNULL(total_return_paid,0),0) + IFNULL(bms_payments.discount,0)) as paid
						");
		$this->db->join("installments","installments.sale_id = sales.id and sales.fee_type = 'Tuition'","left");
		$this->db->join('(SELECT
								sale_id,
								SUM(ABS(grand_total)) AS total_return,
								SUM(paid) AS total_return_paid
							FROM
								'.$this->db->dbprefix('sales').' WHERE sale_status = "returned"
							GROUP BY
								sale_id) as bms_return', 'bms_return.sale_id=sales.id', 'left')
					->join('(SELECT
								sale_id,
								IFNULL(SUM(amount),0) AS paid,
								IFNULL(SUM(discount),0) AS discount
							FROM
								'.$this->db->dbprefix('payments').'
								
							GROUP BY
								sale_id) as bms_payments', 'bms_payments.sale_id=sales.id', 'left');
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,IF(".$this->db->dbprefix("installments").".id > 0,'ITuition',".$this->db->dbprefix("sales").".fee_type)");
		$q = $this->db->get("sales");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->fee_type] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	
	public function getIndexProductFees($academic_year= false, $biller = false,$program = false,$grade = false,$category_id = false,$sub_category_id = false, $product_id = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($category_id || $sub_category_id){
			if($category_id){
				$this->db->where("products.category_id",$category_id);
			}
			if($sub_category_id){
				$this->db->where("products.subcategory_id",$sub_category_id);
			}
		}
		if($product_id){
			$this->db->where("sale_items.product_id",$product_id);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							products.id,
							products.code,
							products.name
						");
		$this->db->join("sales","sale_items.sale_id = sales.id","inner");
		$this->db->join("products","products.id = sale_items.product_id","inner");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("products.id");
		$q = $this->db->get("sale_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexProductSales($academic_year= false, $biller = false,$program = false,$grade = false,$category_id = false,$sub_category_id = false, $product_id = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($category_id || $sub_category_id){
			$this->db->join("products","products.id = sale_items.product_id","inner");
			if($category_id){
				$this->db->where("products.category_id",$category_id);
			}
			if($sub_category_id){
				$this->db->where("products.subcategory_id",$sub_category_id);
			}
		}
		if($product_id){
			$this->db->where("sale_items.product_id",$product_id);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							sales.biller_id,
							sale_items.product_id,
							sum(".$this->db->dbprefix('sale_items').".quantity) as quantity,
							sum(".$this->db->dbprefix('sale_items').".subtotal) as subtotal,
							sum(".$this->db->dbprefix('sale_items').".subtotal) / sum(".$this->db->dbprefix('sale_items').".quantity) as unit_price
							
						");
		$this->db->join("sales","sale_items.sale_id = sales.id","inner");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,sale_items.product_id");
		$q = $this->db->get("sale_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->product_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getMainCategories(){
		$this->db->where("IFNULL(parent_id,0)",0);
		$q = $this->db->get("categories");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getSubCategories($parent_id = false){
		if($parent_id){
			$this->db->where("IFNULL(parent_id,0)",$parent_id);	
		}
		$this->db->where("IFNULL(parent_id,0) > ",0);
		$q = $this->db->get("categories");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexCategorySales($academic_year= false, $biller = false,$program = false,$grade = false,$category_id = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($category_id){
			$this->db->where("products.category_id",$category_id);
		}
		$this->db->select("
							sales.biller_id,
							products.category_id,
							sum(".$this->db->dbprefix('sale_items').".quantity) as quantity,
							sum(".$this->db->dbprefix('sale_items').".subtotal) as subtotal,
							sum(".$this->db->dbprefix('sale_items').".subtotal) / sum(".$this->db->dbprefix('sale_items').".quantity) as unit_price
							
						");
		$this->db->join("sales","sale_items.sale_id = sales.id","inner");
		$this->db->join("products","products.id = sale_items.product_id","inner");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,products.category_id");
		$q = $this->db->get("sale_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->category_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexSubCategorySales($academic_year= false, $biller = false,$program = false,$grade = false,$category_id = false,$sub_category_id = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($category_id){
			$this->db->where("products.category_id",$category_id);
		}
		if($sub_category_id){
			$this->db->where("products.subcategory_id",$sub_category_id);
		}
		$this->db->select("
							sales.biller_id,
							products.subcategory_id,
							sum(".$this->db->dbprefix('sale_items').".quantity) as quantity,
							sum(".$this->db->dbprefix('sale_items').".subtotal) as subtotal,
							sum(".$this->db->dbprefix('sale_items').".subtotal) / sum(".$this->db->dbprefix('sale_items').".quantity) as unit_price
							
						");
		$this->db->join("sales","sale_items.sale_id = sales.id","inner");
		$this->db->join("products","products.id = sale_items.product_id","inner");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,products.subcategory_id");
		$q = $this->db->get("sale_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->subcategory_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	
	public function getSalePayments($academic_year= false, $biller = false,$program = false,$grade = false,$class_id = false, $student = false, $start_date = false, $end_date = false, $received_by = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($program){
			$this->db->where("sales.program_id",$program);
		}
		if($grade){
			$this->db->where("sales.grade_id",$grade);
		}
		if($student){
			$this->db->where("sh_students.id",$student);
		}
		if($class_id){
			$this->db->where("sh_study_infos.class_id",$class_id);
		}
		if($start_date){
			$this->db->where("payments.date >=",$this->bpas->fld($start_date));
		}
		if($end_date){
			$this->db->where("payments.date <=",$this->bpas->fld($end_date));
		}
		if($received_by){
			$this->db->where("payments.created_by",$received_by);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							sales.biller,
							sales.customer,
							sh_students.number,
							sh_students.dob,
							sh_students.firstname,
							sh_students.lastname,
							sales.child_no,
							sales.academic_year,
							sh_classes.name as class_name,
							payments.reference_no,
							payments.date,
							payments.amount,
							IFNULL(".$this->db->dbprefix('cash_accounts').".id,'other') as cash_account_id,
							CONCAT(created_by.last_name,' ',created_by.first_name) as created_by,
							IFNULL(".$this->db->dbprefix('cash_accounts').".name,".$this->db->dbprefix('payments').".paid_by) as paid_by,
							sh_study_infos.type
						");
		$this->db->join("sales","sales.id = payments.sale_id","left");				
		$this->db->join("companies","companies.id = sales.customer_id","left");
		$this->db->join("sh_students","sh_students.id = companies.student_id","left");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = companies.student_id AND sh_study_infos.academic_year = sales.academic_year AND sh_study_infos.program_id = sales.program_id AND sh_study_infos.grade_id = sales.grade_id", "left");
		$this->db->join("sh_classes","sh_classes.id = sh_study_infos.class_id","left");
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$this->db->join("users as created_by","created_by.id = payments.created_by","left");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("payments.id");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexAccountPayment($academic_year= false, $biller = false, $start_date = false, $end_date = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($start_date){
			$this->db->where("payments.date >=",$this->bpas->fld($start_date));
		}
		if($end_date){
			$this->db->where("payments.date <=",$this->bpas->fld($end_date));
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		$this->db->select("
							sales.biller_id,
							SUM(".$this->db->dbprefix("payments").".amount) as amount,
							IFNULL(".$this->db->dbprefix('cash_accounts').".id,'other') as paid_by
						");
		$this->db->join("sales","sales.id = payments.sale_id","left");				
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,IFNULL(".$this->db->dbprefix('cash_accounts').".id,'other')");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->paid_by] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexAnnualPayment($academic_year= false, $biller = false, $year = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		if($year){
			$this->db->where("YEAR(".$this->db->dbprefix('payments').".date)",$year);
		}

		$this->db->select("
							sales.biller_id,
							SUM(".$this->db->dbprefix("payments").".amount) as amount,
							MONTH(".$this->db->dbprefix('payments').".date) as month
						");
		$this->db->join("sales","sales.id = payments.sale_id","left");				
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,MONTH(".$this->db->dbprefix('payments').".date)");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->month] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexDailyPayment($academic_year= false, $biller = false, $month = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		if($month){
			$month = explode("/",$month);
			$this->db->where("MONTH(".$this->db->dbprefix('payments').".date)",$month[0]);
			$this->db->where("YEAR(".$this->db->dbprefix('payments').".date)",$month[1]);
		}

		$this->db->select("
							sales.biller_id,
							SUM(".$this->db->dbprefix("payments").".amount) as amount,
							DATE(".$this->db->dbprefix('payments').".date) as day
						");
		$this->db->join("sales","sales.id = payments.sale_id","left");				
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,DATE(".$this->db->dbprefix('payments').".date)");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->day] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexDailyPaymentByCash($academic_year= false, $biller = false, $month = false, $fee_type = false){
		if($academic_year){
			$this->db->where("sales.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sales.biller_id",$biller);
		}
		if($fee_type){
			$this->db->where("sales.fee_type",$fee_type);
		}
		if($month){
			$month = explode("/",$month);
			$this->db->where("MONTH(".$this->db->dbprefix('payments').".date)",$month[0]);
			$this->db->where("YEAR(".$this->db->dbprefix('payments').".date)",$month[1]);
		}

		$this->db->select("
							sales.biller_id,
							SUM(".$this->db->dbprefix("payments").".amount) as amount,
							DATE(".$this->db->dbprefix('payments').".date) as day,
							IFNULL(".$this->db->dbprefix('cash_accounts').".id,'other') as cash_account_id
						");
		$this->db->join("sales","sales.id = payments.sale_id","left");		
		$this->db->join("cash_accounts","cash_accounts.id = payments.paid_by","left");		
		$this->db->where("sales.module_type","school");
		$this->db->group_by("sales.biller_id,DATE(".$this->db->dbprefix('payments').".date),IFNULL(".$this->db->dbprefix('cash_accounts').".id,'other')");
		$q = $this->db->get("payments");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->day][$row->cash_account_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getStudentByInfos($academic_year= false, $biller = false, $program = false, $grade = false, $class = false, $student = false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_study_infos.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_study_infos.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_study_infos.grade_id",$grade);
		}
		if($class){
			$this->db->where("sh_study_infos.class_id",$class);
		}
		if($student){
			$this->db->where("sh_study_infos.student_id",$student);
		}
		$this->db->select("sh_students.*,cities.zone_name as city,districts.zone_name as district,communes.zone_name as commune,sh_study_infos.type,sh_study_infos.status");
		$this->db->join("sh_study_infos","sh_students.id = sh_study_infos.student_id","left");
		$this->db->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE IFNULL(city_id,0) = 0) as cities","cities.id = sh_students.city_id","left");
		$this->db->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE city_id > 0 AND IFNULL(district_id,0) = 0) as districts","districts.id = sh_students.district_id","left");
        $this->db->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE district_id > 0 AND IFNULL(commune_id,0) = 0) as communes","communes.id = sh_students.commune_id","left");
		$this->db->group_by("sh_students.id");
		$q = $this->db->get("sh_students");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexStudentClasses($academic_year= false, $student = false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($student){
			$this->db->where("sh_study_infos.student_id",$student);
		}
		$this->db->select("
							sh_study_infos.student_id,
							sh_study_infos.program_id,
							sh_classes.name as class_name
						");
		$this->db->join("sh_classes","sh_classes.id = sh_study_infos.class_id","left");
		$this->db->group_by("sh_study_infos.student_id,sh_study_infos.program_id");
		$q = $this->db->get("sh_study_infos");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->student_id][$row->program_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexSiblings($academic_year= false){
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		$this->db->select("sh_students.family_id,sh_study_infos.child_no,sh_students.lastname,sh_students.firstname,companies.company as biller_name");
		$this->db->join("sh_study_infos","sh_students.id = sh_study_infos.student_id","left");
		$this->db->join("companies","companies.id = sh_study_infos.biller_id","left");
		$this->db->group_by("sh_students.id");
		$q = $this->db->get("sh_students");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->family_id][$row->child_no] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexFamalies($type="family"){
		if($type){
			$this->db->where("sh_student_families.type",$type);
		}
		$this->db->select("sh_student_families.*,cities.zone_name as city,
				districts.zone_name as district,communes.zone_name as commune");
		$this->db->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE IFNULL(city_id,0) = 0) as cities","cities.id = sh_student_families.city_id","left");
		$this->db->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE city_id > 0 AND IFNULL(district_id,0) = 0) as districts","districts.id = sh_student_families.district_id","left");
        $this->db->join("(SELECT id,zone_name FROM ".$this->db->dbprefix('zones')." WHERE district_id > 0 AND IFNULL(commune_id,0) = 0) as communes","communes.id = sh_student_families.commune_id","left");
		$q = $this->db->get("sh_student_families");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$relationship = $row->relationship;
				if($relationship != "Father" && $relationship != "Mother"){
					$relationship == "Other";
				}
                $data[$row->family_id][$relationship] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getMaxChildNo(){
		$this->db->select("sh_students.child_no");
		$this->db->order_by("sh_students.child_no","desc");
		$this->db->limit(1);
		$q = $this->db->get("sh_students");
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	
	public function getFeedBackByGroup($group_id = false){
		$q = $this->db->get_where("sh_feedback_questions",array("group_id"=>$group_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addTicket($data = false, $items = false){
		if($this->db->insert("sh_tickets",$data)){
			$ticket_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["ticket_id"] = $ticket_id;
					$this->db->insert("sh_ticket_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateTicket($id = false, $data = false, $items = false){
		if($id && $this->db->update("sh_tickets",$data,array("id"=>$id))){
			$this->db->delete("sh_ticket_items",array("ticket_id"=>$id));
			if($items){
				$this->db->insert_batch("sh_ticket_items",$items);
			}
			return true;
		}
		return false;
	}
	
	public function updateTicketStatus($id = false, $data = false){
		if($id && $this->db->update("sh_tickets",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	public function deleteTicket($id = false){
		if($id && $this->db->delete("sh_tickets",array("id"=>$id))){
			$this->db->delete("sh_ticket_items",array("ticket_id"=>$id));
			return true;
		}
		return false;
	}

	
	public function getTicketByID($id = false){
		$q = $this->db->get_where("sh_tickets", array("id" => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getTicketByItems($id = false, $sort = "acs"){
		$this->db->order_by("sh_ticket_items.id",$sort);
		$q = $this->db->get_where("sh_ticket_items",array("ticket_id"=>$id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTicketItemByGroup($ticket_id = false, $group_id = false){
		if($ticket_id){
			$this->db->where("ticket_id",$ticket_id);
		}
		if($group_id){
			$this->db->where("group_id",$group_id);
		}
		$q = $this->db->get("sh_ticket_items");
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function getTicketByStudent($student_id = false, $not_id = false){
		if($student_id){
			$student = $this->getStudentByID($student_id);
			$this->db->where("sh_tickets.student_id IN (SELECT id from ".$this->db->dbprefix('sh_students')." WHERE family_id = ".$student->family_id.")");
		}
		if($not_id){
			$this->db->where("sh_tickets.id !=",$not_id);
		}
		$this->db->select("sh_tickets.id,sh_tickets.reference_no");
		$q = $this->db->get("sh_tickets");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getSolutionByID($id = false){
		$q = $this->db->get_where("sh_solutions",array("id"=>$id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addSolution($data = false){
		if($this->db->insert("sh_solutions",$data)){
			$this->synceTicket($data['ticket_id']);
			return true;
		}
		return false;
	}
	public function updateSolution($id = false, $data = false){
		if($id && $this->db->update("sh_solutions",$data,array("id"=>$id))){
			$this->synceTicket($data['ticket_id']);
			return true;
		}
		return false;
	}

	public function deleteSolution($id = false){
		$solution = $this->getSolutionByID($id);
		if($id && $this->db->delete("sh_solutions",array("id"=>$id))){
			$this->synceTicket($solution->ticket_id);
			return true;
		}
		return false;
	}
	
	public function getSolutionByTicketID($ticket_id = false, $staus = false){
		if($staus){
			$this->db->where("sh_solutions.status",$staus);
		}
		$q = $this->db->get_where("sh_solutions",array("ticket_id"=>$ticket_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function synceTicket($ticket_id = false){
		$ticket = $this->getTicketByID($ticket_id);
		if($ticket && $ticket->status != "completed" && $ticket->status != "new_ticket"){
			$status = "pending";
			$solutions = $this->getSolutionByTicketID($ticket_id,"send");
			if($solutions){
				$status = "solved";
			}else if($ticket->solver > 0){
				$status = "assigned";
			}
			$this->db->update("sh_tickets",array("status"=>$status),array("id"=>$ticket_id));
		}
	}
	
	public function addTicketResponse($data = false){
		if($this->db->insert("sh_ticket_responses",$data)){
			$this->db->update("sh_tickets",array("status"=>$data["status"]),array("id"=>$data["ticket_id"]));
			return true;
		}
		return false;
	}
	

	public function getTicketReport($academic_year= false, $biller = false,$program = false,$grade = false,$class_id = false,$family_id = false, $teacher_id = false,$student_id = false){
		if($academic_year){
			$this->db->where("sh_tickets.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_tickets.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_tickets.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_tickets.grade_id",$grade);
		}
		if($class_id){
			$this->db->where("sh_tickets.class_id",$class_id);
		}
		if($family_id){
			$this->db->where("sh_tickets.family_id",$family_id);
		}
		if($teacher_id){
			$this->db->where("sh_class_years.teacher_id",$teacher_id);
		}
		if($student_id){
			$this->db->where("sh_tickets.student_id",$student_id);
		}
		
		$this->db->select("
						sh_tickets.id, 
						sh_tickets.date,
						companies.company as biller,
						sh_tickets.reference_no,
						CONCAT(".$this->db->dbprefix('sh_students').".lastname,'  ',".$this->db->dbprefix('sh_students').".firstname) as student_name,
						sh_tickets.family_name, 
						sh_class_years.teacher_name,
						sh_tickets.phone,
						CONCAT(".$this->db->dbprefix('sh_tickets').".academic_year,' - ',".$this->db->dbprefix('sh_tickets').".academic_year + 1) as academic_year,
						sh_grades.name as grade,
						sh_classes.name as class,
						sh_tickets.status,
						sh_tickets.attachment
					")
			->join("sh_students","sh_students.id = sh_tickets.student_id","left")
			->join("companies","companies.id = sh_tickets.biller_id","left")
			->join('sh_grades','sh_grades.id = sh_tickets.grade_id','left')
			->join('sh_classes','sh_classes.id = sh_tickets.class_id','left')
			->join('sh_class_years','sh_class_years.class_id = sh_tickets.class_id AND sh_class_years.academic_year = sh_tickets.academic_year','left');
		$q = $this->db->get("sh_tickets");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getIndexTicketItems($academic_year= false, $biller = false,$program = false,$grade = false,$class_id = false,$family_id = false, $student_id = false){
		if($academic_year){
			$this->db->where("sh_tickets.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_tickets.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_tickets.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_tickets.grade_id",$grade);
		}
		if($class_id){
			$this->db->where("sh_tickets.class_id",$class_id);
		}
		if($family_id){
			$this->db->where("sh_tickets.family_id",$family_id);
		}
		if($student_id){
			$this->db->where("sh_tickets.student_id",$student_id);
		}
		$this->db->select("sh_ticket_items.*,sh_feedback_question_groups.name as group_name");
		$this->db->join("sh_tickets","sh_tickets.id = sh_ticket_items.ticket_id","inner");
		$this->db->join("sh_feedback_question_groups","sh_feedback_question_groups.id = sh_ticket_items.group_id","left");
		$this->db->order_by("sh_ticket_items.group_id");
		$q = $this->db->get("sh_ticket_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->ticket_id][] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getIndexTicketSolutions($academic_year= false, $biller = false,$program = false,$grade = false,$class_id = false,$family_id = false, $student_id = false){
		if($academic_year){
			$this->db->where("sh_tickets.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_tickets.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_tickets.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_tickets.grade_id",$grade);
		}
		if($class_id){
			$this->db->where("sh_tickets.class_id",$class_id);
		}
		if($family_id){
			$this->db->where("sh_tickets.family_id",$family_id);
		}
		if($student_id){
			$this->db->where("sh_tickets.student_id",$student_id);
		}
		$this->db->where("sh_solutions.status","send");
		$this->db->select("sh_solutions.*");
		$this->db->join("sh_tickets","sh_tickets.id = sh_solutions.ticket_id","inner");
		$q = $this->db->get("sh_solutions");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->ticket_id][] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	public function getIndexFeedback(){
		$q = $this->db->get("sh_feedback_questions");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->id]= $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTicketFamaily(){
		$this->db->select("sh_student_families.*");
		$this->db->join("sh_student_families","sh_student_families.id = sh_tickets.family_id","inner");
		$this->db->group_by("sh_tickets.family_id");
		$q = $this->db->get("sh_tickets");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[]= $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getWaitingByID($id = false){
		$q = $this->db->get_where("sh_waitings", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getWaitingItem($waiting_id = false){
		if($waiting_id){
			$this->db->where("sh_waiting_items.waiting_id",$waiting_id);
		}
		$this->db->select("sh_waiting_items.*,sh_grades.name as grade_name,old_grades.name as old_grade_name");
		$this->db->join("sh_grades","sh_grades.id = sh_waiting_items.grade_id","left");
		$this->db->join("(SELECT * FROM ".$this->db->dbprefix('sh_grades').") as old_grades","old_grades.id = sh_waiting_items.old_grade_id","left");
		$q = $this->db->get("sh_waiting_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[]= $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getStudentWaitingID($id = false){
		$this->db->select("sh_waitings.*,sh_waiting_items.*,sh_waiting_items.gender as student_gender,sh_waiting_items.id as item_waiting_id");
		$this->db->join("sh_waitings","sh_waitings.id = sh_waiting_items.waiting_id","inner");
		$q = $this->db->get_where("sh_waiting_items", array('sh_waiting_items.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}

	public function addWaiting($data = false, $items = false){
		if($this->db->insert("sh_waitings",$data)){
			$waiting_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["waiting_id"] = $waiting_id;
					$this->db->insert("sh_waiting_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateWaiting($id = false, $data = false, $items = false){
		if($id && $this->db->update("sh_waitings",$data, array("id"=>$id))){
			$this->db->delete("sh_waiting_items",array("waiting_id"=>$id));
			if($items){
				$this->db->insert_batch("sh_waiting_items",$items);
			}
			return true;
		}
		return false;
	}
	
	public function deleteWaiting($id = false){
		if($id && $this->db->delete("sh_waitings",array("id"=>$id))){
			$this->db->delete("sh_waiting_items",array("waiting_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function getFollowUpByWaiting($waiting_id = false){
		$q = $this->db->get_where("sh_follow_ups",array("waiting_id"=>$waiting_id));
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[]= $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getFollowUpByID($id = false){
		$q = $this->db->get_where("sh_follow_ups", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	} 
	public function addFollowUp($data = false){
		if($this->db->insert("sh_follow_ups",$data)){
			$waiting_id = $this->db->insert_id();
			$this->synceWaiting($waiting_id);
			return true;
		}
		return false;
	}
	public function updateFollowUp($id = false, $data = false){
		if($id && $this->db->update("sh_follow_ups",$data, array("id"=>$id))){
			$this->synceWaiting($data['waiting_id']);
			return true;
		}
		return false;
	}
	public function deleteFollowUp($id = false){
		$follow_up = $this->getFollowUpByID($id);
		if($id && $this->db->delete("sh_follow_ups",array("id"=>$id))){
			$this->synceWaiting($follow_up->waiting_id);
			return true;
		}
		return false;
	}
	
	public function synceWaiting($waiting_id = false){
		$follow_up = $this->getFollowUpByWaiting($waiting_id);
		$status = "pending";
		if($follow_up){
			$status = "follow_up";
		}
		$this->db->update("sh_waitings",array("status"=>$status),array("id"=>$waiting_id));
	}
	
	public function getTestingByID($id = false){
		$this->db->select("sh_testings.*,sh_testing_groups.testing_date,sh_testing_groups.name as group_name,sh_testing_groups.close_enrollment_date,sh_testing_results.code as result_code,sh_testing_results.name as result_name");
		$this->db->join("sh_testing_results","sh_testing_results.id = sh_testings.result_id","left");
		$this->db->join("sh_testing_groups","sh_testing_groups.id = IF(".$this->db->dbprefix('sh_testings').".regroup_id > 0, ".$this->db->dbprefix('sh_testings').".regroup_id, ".$this->db->dbprefix('sh_testings').".group_id)","left");
		$q = $this->db->get_where("sh_testings", array('sh_testings.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function getTestingItems($testing_id = false){
		if($testing_id){
			$this->db->where("sh_testing_items.testing_id",$testing_id);
		}
		$this->db->select("sh_testing_items.*,sh_programs.name as program_name,sh_grades.name as grade_name,new_grades.name as new_grade_name");
		$this->db->join("sh_programs","sh_programs.id = sh_testing_items.program_id","left");
		$this->db->join("sh_grades","sh_grades.id = sh_testing_items.o_grade","left");
		$this->db->join("(SELECT * FROM ".$this->db->dbprefix('sh_grades').") as bms_new_grades ","new_grades.id = sh_testing_items.n_grade","left");
		$q = $this->db->get("sh_testing_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[]= $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addTesting($data = false, $items = false){
		if($this->db->insert("sh_testings",$data)){
			$testing_id = $this->db->insert_id();
			if($items){
				foreach($items as $item){
					$item["testing_id"] = $testing_id;
					$this->db->insert("sh_testing_items",$item);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateTesting($id = false, $data = false, $items = false){
		if($id && $this->db->update("sh_testings",$data, array("id"=>$id))){
			$this->db->delete("sh_testing_items",array("testing_id"=>$id));
			if($items){
				$this->db->insert_batch("sh_testing_items",$items);
			}
			return true;
		}
		return false;
	}
	
	public function deleteTesting($id = false){
		if($id && $this->db->delete("sh_testings",array("id"=>$id))){
			$this->db->delete("sh_testing_items",array("testing_id"=>$id));
			return true;
		}
		return false;
	}
	
	public function setTesting($id = false, $data = false){
		if($id && $this->db->update("sh_testings",$data, array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	public function sendTesting($id = false){
		if($id){
			$testing = $this->getTestingByID($id);
			$biller_details = $this->site->getCompanyByID($testing->biller_id);
			$product = $this->site->getProductByID($this->Settings->testing_fee);
			$sale_id = false;
			if($product){
				$sale_items[] = array(
					'product_id' => $product->id,
					'product_code' => $product->code,
					'product_name' => $product->name,
					'product_type' => $product->type,
					'net_unit_price' => $product->price,
					'unit_price' => $product->price,
					'real_unit_price' => $product->price,
					'quantity' => 1,
					'unit_quantity' => 1,
					'warehouse_id' => $this->Settings->default_warehouse,
					'subtotal' =>  $product->price
				);
				$reference_no =  $this->site->getReference('so',$testing->biller_id);
				$sale = array(
					'date' => $testing->date,
					'reference_no' => $reference_no,
					'customer_id' => $this->Settings->default_customer,
					'customer' => $testing->customer,
					'biller_id' => $testing->biller_id,
					'biller' => $biller_details->company,
					'warehouse_id' => $this->Settings->default_warehouse,
					'total_items' => 1,
					'total' => $product->price,
					'grand_total' => $product->price,
					'sale_status' => "completed",
					'payment_status' => "pending",
					'delivery_status' => 'completed',
					'type' => 'school',
					'academic_year' => $testing->academic_year,
					'created_by' => $this->session->userdata('user_id'),
					'fee_type' => "Other"
				);
				if($this->db->insert("sales",$sale)){
					$sale_id = $this->db->insert_id();
					foreach($sale_items as $sale_item){
						$sale_item["sale_id"] = $sale_id;
						$this->db->insert("sale_items",$sale_item);
					}
				}
			}
			$this->setTesting($id,array("status"=>"sent","sale_id"=>$sale_id));
			return true;
		}
		return false;
	}
	
	public function getTestingBySaleID($sale_id = false){
		$this->db->select("sh_testings.*,sh_testing_groups.testing_date");
		$this->db->join("sh_testing_groups","sh_testing_groups.id = sh_testings.group_id","left");
		$q = $this->db->get_where("sh_testings",array("sh_testings.sale_id"=>$sale_id));
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function getWatingStudents(){
		$this->db->select("sh_waiting_items.id,sh_waiting_items.student");
		$this->db->join("sh_waitings","sh_waitings.id = sh_waiting_items.waiting_id","inner");
		$this->db->join("sh_testings","sh_testings.stname = sh_waiting_items.student AND sh_testings.customer = sh_waitings.name","LEFT");
		$this->db->where("sh_testings.id",null);
		$this->db->group_by("sh_waiting_items.id");
		$q = $this->db->get("sh_waiting_items");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[]= $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getOtherSchools(){
		$q = $this->db->get("sh_other_schools");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[]= $row;
            }
            return $data;
        }
        return FALSE;
		
	}
	
	public function getTestingGroups($academic_year = false){
		if($academic_year){
			$this->db->where("sh_testing_groups.academic_year",$academic_year);
		}
		$q = $this->db->get("sh_testing_groups");
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	
	public function getTestingGroupByID($id = false){
		$q = $this->db->get_where("sh_testing_groups",array("sh_testing_groups.id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function addTestingGroup($data = false){
		if($data && $this->db->insert("sh_testing_groups",$data)){
			return true;
		}
		return false;
	}
	public function updateTestingGroup($id = false, $data = false){
		if($id && $this->db->update("sh_testing_groups",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function deleteTestingGroup($id = false){
		if($id && $this->db->delete("sh_testing_groups",array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	
	public function getTestings($academic_year = false,$biller_id = false,$group_id = false,$start_date = false,$end_date = false,$testing_date = false,$status = false,$result_status = false,$assign_grade = false){
		if ($academic_year) {
			$this->db->where('sh_testings.academic_year', $academic_year);
		}
		if ($biller_id) {
			$this->db->where('sh_testings.biller_id', $biller_id);
		}
		if ($start_date) {
			$this->db->where('sh_testings.date >=', $start_date);
		}
		if ($end_date) {
			$this->db->where('sh_testings.date <=', $end_date);
		}
		if ($testing_date) {
			$this->db->where('sh_testing_groups.testing_date', $testing_date);
		}
		if ($group_id) {
			$this->db->where('sh_testings.group_id', $group_id);
		}
		if ($status) {
			$this->db->where("(IF(".$this->db->dbprefix("sales").".payment_status = 'paid', 'paid',".$this->db->dbprefix("sh_testings").".status)) = ", $status);
		}
		if ($result_status) {
			$this->db->where("IFNULL(".$this->db->dbprefix("sh_testings").".result_status,'pending')", $result_status);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->db->where('sh_testings.biller_id', $this->session->userdata('biller_id'));
		}
		
		$select = "";
		if($assign_grade){
			$this->db->join("sh_grades","sh_grades.id = sh_testings.testing_grade_id","inner");
			$select = "sh_grades.name as testing_grade,sh_testings.testing_grade_id,";
		}
		
		$this->db->select("sh_testings.*,
							".$select."
							companies.company,
							sh_testing_groups.name as group_name,
							sh_testing_groups.testing_date,
							sh_testing_results.name as result_name,
							IF(".$this->db->dbprefix("sales").".payment_status = 'paid', 'paid',".$this->db->dbprefix("sh_testings").".status) as status,
							IFNULL(".$this->db->dbprefix("sh_testings").".result_status,'pending') as result_status,
						", FALSE)
				->join("sh_testing_groups","sh_testing_groups.id = IF(".$this->db->dbprefix('sh_testings').".regroup_id > 0, ".$this->db->dbprefix('sh_testings').".regroup_id, ".$this->db->dbprefix('sh_testings').".group_id)","left")
				->join("sh_testing_results","sh_testing_results.id = sh_testings.result_id","left")
				->join('companies','companies.id = sh_testings.biller_id','left')
				->join("sales","sales.id = sh_testings.sale_id","left")
				->group_by("sh_testings.id")
				->order_by("sh_testings.id","desc");
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexTestingItems($academic_year = false,$biller_id = false,$group_id = false,$start_date = false,$end_date = false,$testing_date = false){
		if ($academic_year) {
			$this->db->where('sh_testings.academic_year', $academic_year);
		}
		if ($biller_id) {
			$this->db->where('sh_testings.biller_id', $biller_id);
		}
		if ($start_date) {
			$this->db->where('sh_testings.date >=', $start_date);
		}
		if ($end_date) {
			$this->db->where('sh_testings.date <=', $end_date);
		}
		if ($testing_date) {
			$this->db->where('sh_testing_groups.testing_date', $testing_date);
		}
		if ($group_id) {
			$this->db->where('sh_testings.group_id', $group_id);
		}
		$this->db->select("sh_testing_items.testing_id,sh_programs.name as program_name,sh_grades.name as grade_name,IFNULL(o_sh_grades.name,'N/A') as o_grade_name");
		$this->db->join("sh_testing_groups","sh_testing_groups.id = sh_testings.group_id","left");
		$this->db->join("sh_testing_items","sh_testing_items.testing_id = sh_testings.id","inner");
		$this->db->join("sh_programs","sh_programs.id = sh_testing_items.program_id","inner");
		$this->db->join("sh_grades","sh_grades.id = sh_testing_items.n_grade","inner");
		$this->db->join("sh_grades as o_sh_grades","o_sh_grades.id = sh_testing_items.o_grade","left");
		$this->db->group_by("sh_testing_items.id");
		$this->db->order_by("sh_testing_items.program_id");
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->testing_id][] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function addStudentStatus($data = false)
	{
		if($data) {
			foreach($data as $row){
				if($this->db->insert("sh_student_statuses", $row)){
					$status_id = $this->db->insert_id();
					$this->syncStudentStatus($status_id);
				}
			}
			return true;
		}
		return false;
	}
	
	public function updateStudentStatus($id = false,$data = false){
		if($data && $this->deleteStudentStatus($id)){
			foreach($data as $row){
				if($this->db->insert("sh_student_statuses",$row)){
					$status_id = $this->db->insert_id();
					$this->syncStudentStatus($status_id);
				}
			}
			return true;
		}
	}
	
	public function reviewStudentStatus($id = false,$data = false){
		if($id && $this->db->update("sh_student_statuses",$data, array("id"=>$id))){
			$this->syncStudentStatus($id);
			return true;
		}
	}
	
	public function deleteStudentStatus($id = false){
		$student_status = $this->getStudentStatusByID($id);
		if($id && $this->db->delete("sh_student_statuses",array("id"=>$id))){
			$this->syncStudentStatus(false , $student_status->student_id, $student_status->academic_year);
			return true;
		}
		return false;
	}
	
	public function getStudentStatusByID($id = false)
	{
		$this->db->select("
			sh_student_statuses.*,
			sh_students.number,
			sh_students.code,
			sh_students.gender,
			sh_students.lastname,
			sh_students.firstname,
			sh_students.lastname_other,
			sh_students.firstname_other,
			{$this->db->dbprefix('sh_programs')}.id AS program_id,
			{$this->db->dbprefix('sh_programs')}.name AS program,
			{$this->db->dbprefix('sh_skills')}.id AS skill_id,
			{$this->db->dbprefix('sh_skills')}.name AS skill,
			{$this->db->dbprefix('sh_grades')}.id AS grade_id,
			{$this->db->dbprefix('sh_grades')}.name AS grade,
			{$this->db->dbprefix('sh_sections')}.id AS section_id,
			{$this->db->dbprefix('sh_sections')}.name AS section,
			{$this->db->dbprefix('sh_classes')}.id AS class_id,
			{$this->db->dbprefix('sh_classes')}.name AS class,
			{$this->db->dbprefix('sh_classes')}.name AS class_name,
			{$this->db->dbprefix('sh_rooms')}.id AS room_id,
			{$this->db->dbprefix('sh_rooms')}.name AS room,
			{$this->db->dbprefix('custom_field')}.id AS timeshift_id,
			{$this->db->dbprefix('custom_field')}.name AS timeshift
		");
		$this->db->join("sh_students","sh_students.id = sh_student_statuses.student_id", "left");
		$this->db->join("sh_study_infos","sh_study_infos.id = sh_student_statuses.study_info_id", "left");
		$this->db->join("sh_programs","sh_programs.id = sh_study_infos.program_id", "left");
		$this->db->join("sh_skills","sh_skills.id = sh_study_infos.skill_id", "left");
		$this->db->join("sh_grades","sh_grades.id = sh_study_infos.grade_id", "left");
		$this->db->join("sh_sections","sh_sections.id = sh_study_infos.section_id", "left");
		$this->db->join("sh_classes","sh_classes.id = sh_study_infos.class_id", "left");
		$this->db->join("sh_rooms","sh_rooms.id = sh_study_infos.room_id", "left");
		$this->db->join("custom_field","custom_field.id = sh_study_infos.timeshift_id", "left");
		$q = $this->db->get_where("sh_student_statuses", array('sh_student_statuses.id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function syncStudentStatus($status_id = false, $student_id = false, $academic_year = false)
	{
		if($status_id){
			$student_status = $this->getStudentStatusByID($status_id);
			$academic_year  = $student_status->academic_year;
			$this->db->query("
				UPDATE " . $this->db->dbprefix('sh_study_infos') . "
				INNER JOIN " . $this->db->dbprefix('sh_student_statuses') . " ON " . $this->db->dbprefix('sh_student_statuses') . ".student_id = " . $this->db->dbprefix('sh_study_infos') . ".student_id 
					AND " . $this->db->dbprefix('sh_student_statuses') . ".study_info_id = " . $this->db->dbprefix('sh_study_infos') . ".id
				SET 
					" . $this->db->dbprefix('sh_study_infos') . ".`status` = 
					IF(
						IFNULL( " . $this->db->dbprefix('sh_student_statuses') . ".id, 0 ) = 0,
							'active',
							IF(" . $this->db->dbprefix('sh_student_statuses') . ".review_status = 'accepted', 
								'active', 
								IF(" . $this->db->dbprefix('sh_student_statuses') . ".review_status = 'black_list', 
									'black_list', 
									" . $this->db->dbprefix('sh_student_statuses') . ".`status`)
								)
							)
				WHERE " . $this->db->dbprefix('sh_student_statuses') . ".id = " . $student_status->id . "
			");

			if($student_status->status == "assign"){
				$this->db->update("sh_students", array("is_admission" => 0), array("id" => $student_status->student_id));
			} else if($student_status->status == "drop_out" || $student_status->status == "graduate"){
				$this->syncStudentChildNo($student_status->student_id, "inactive", $academic_year);
			} else if($student_status->status == "black_list" || $student_status->review_status == "black_list"){
				if($families = $this->getFamilyByStudent($student_status->student_id, "family")){
					$father   = "";
					$mother   = "";
					$guardian = "";
					foreach($families as $family){
						if($family->relationship == "Father"){
							$father = $family->full_name;
						} else if ($family->relationship == "Mother"){
							$mother = $family->full_name;
						} else {
							$guardian = $family->full_name;
						}
					}
					if($father || $mother || $guardian){
						$black_list = array(
							'student_id'  => $student_status->student_id,
							'father'      => $father,
							'mother'      => $mother,
							'guardian'    => $guardian,
							'description' => $student_status->note,
							'status'      => "active"
						);
						if($this->getBlackListByStudentID($student_status->student_id)){
							$this->db->update("sh_black_lists", $black_list, array("student_id" => $student_status->student_id));
						}else{
							$this->db->insert("sh_black_lists", $black_list);
						}
					}
				}
			} else {
				$this->db->update("sh_black_lists", array("status" => "inactive"), array("student_id" => $student_status->student_id));
			}
		} else if ($student_id){
			$this->db->delete("sh_black_lists", array("student_id" => $student_id));
			$this->db->update("sh_students", array("is_admission" => 1), array("id" => $student_id));
			$statuses = false;
			$this->db->where("sh_study_infos.student_id", $student_id);
			$this->db->select("sh_study_infos.id, sh_student_statuses.status, sh_student_statuses.review_status");
			$this->db->join("sh_student_statuses","sh_student_statuses.student_id = sh_study_infos.student_id AND 
				sh_student_statuses.academic_year = sh_study_infos.academic_year AND 
				sh_student_statuses.biller_id = sh_study_infos.biller_id AND 
				sh_student_statuses.study_info_id = sh_study_infos.id", "left");
			$q = $this->db->get("sh_study_infos");
			if ($q->num_rows() > 0) {
				foreach (($q->result()) as $row) {
					if(!$row->status || $row->review_status == "accepted"){
						$status = "active";
					} else if($row->review_status == "black_list"){
						$status = "black_list";
					} else {
						$status = $row->status;
					}
					$statuses[$status] = $status;
					$this->db->update("sh_study_infos", array("status" => $status), array("id" => $row->id));
				}
				if(isset($statuses["drop_out"]) || isset($statuses["graduate"])){
					$student_status = "inactive";
				} else {
					$student_status = "active";
				}
				if(isset($statuses["black_list"])){
					if($families  = $this->getFamilyByStudent($student_id, "family")){
						$father   = "";
						$mother   = "";
						$guardian = "";
						foreach($families as $family){
							if($family->relationship == "Father"){
								$father = $family->full_name;
							} else if ($family->relationship == "Mother"){
								$mother = $family->full_name;
							} else {
								$guardian = $family->full_name;
							}
						}
						if ($father || $mother || $guardian){
							$black_list = array(
								'student_id' => $student_id,
								'father'     => $father,
								'mother'     => $mother,
								'guardian'   => $guardian,
								'status'     => "active"
							);
							if ($this->getBlackListByStudentID($student_id)){
								$this->db->update("sh_black_lists", $black_list, array("student_id"=>$student_id));
							} else {
								$this->db->insert("sh_black_lists", $black_list);
							}
						}
					}
				}
				$this->syncStudentChildNo($student_id, $student_status, $academic_year);
			}
		}
	}
	
	public function syncStudentChildNo($id = false, $status = false, $academic_year = false){
		if($id && $student = $this->getStudentByID($id)){
			if($this->db->update("sh_students",array("sh_students.status"=>$status),array("id"=>$id))){
				$siblings = $this->getSiblings($student->family_id);
				if($siblings){
					$i = 1;
					foreach($siblings as $sibling){
						$this->db->update("sh_students",array("child_no"=>$i),array("id"=>$sibling->id));
						$i++;
					}
				}
			}
		}
	}
	
	public function getStudentName($term = false, $biller_id = false,$academic_year = false,$program_id = false,$grade_id = false, $status= false)
    {
		$limit = $this->Settings->rows_per_page;
		if($biller_id){
			$this->db->where("sh_study_infos.biller_id",$biller_id);
		}
		if($program_id){
			$this->db->where("sh_study_infos.program_id",$program_id);
		}
		if($grade_id){
			$this->db->where("sh_study_infos.grade_id",$grade_id);
		}
		if($status == "suspend"){
			$this->db->join("sh_student_statuses","sh_student_statuses.student_id = sh_study_infos.student_id AND sh_student_statuses.biller_id = sh_study_infos.biller_id AND sh_student_statuses.academic_year = ".$academic_year."", "LEFT");
			$this->db->where("IFNULL(".$this->db->dbprefix('sh_student_statuses').".id,0)",0);
			$this->db->where("(".$this->db->dbprefix('sh_study_infos').".academic_year + 1) >=",$academic_year);
		}else{
			$this->db->where("sh_study_infos.status","active");
			if($academic_year){
				$this->db->where("sh_study_infos.academic_year",$academic_year);
			}
		}
		
        $this->db->select('
							sh_students.id,
							sh_students.number,
							sh_students.code,
							sh_students.gender,
							sh_students.lastname,
							sh_students.firstname,
							sh_students.lastname_other,
							sh_students.firstname_other,
							sh_study_infos.class_id,
							sh_study_infos.class as class_name,
							sh_study_infos.academic_year
						')
			->join("sh_students","sh_study_infos.student_id = sh_students.id","inner")
			->where("(lastname LIKE '%" . $term . "%' OR firstname LIKE '%" . $term . "%' OR lastname_other LIKE '%" . $term . "%' OR firstname_other LIKE '%" . $term . "%' OR number LIKE '%" . $term . "%' OR
				CONCAT(lastname,' ',firstname,' ', ' (', number, ')') LIKE '%" . $term . "%')")
            ->group_by('sh_students.id')->limit($limit);
        $q = $this->db->get('sh_study_infos');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
    }

	public function getStudentStatusName($term = false, $biller_id = false, $academic_year = false, $program_id = false, $skill_id = false, $grade_id = false, $semester_id = false, $class_id = false, $timeshift = false, $status = false)
    {
		$limit = $this->Settings->rows_per_page;
		if($biller_id){
			$this->db->where("sh_study_infos.biller_id", $biller_id);
		}
		if($program_id){
			$this->db->where("sh_study_infos.program_id", $program_id);
		}
		if($skill_id){
			$this->db->where('sh_study_infos.skill_id', $skill_id);
		}
		if($grade_id){
			$this->db->where("sh_study_infos.grade_id", $grade_id);
		}
		if($semester_id){
			$this->db->where('sh_study_infos.section_id', $semester_id);
		}
		if($class_id){
			$this->db->where('sh_study_infos.class_id', $class_id);
		}
		if($timeshift){
			$this->db->where('sh_study_infos.timeshift_id', $timeshift);
		} else {
			$this->db->where('sh_study_infos.timeshift_id', null);
		}
		if($status == "suspend"){
			$this->db->join("sh_student_statuses","sh_student_statuses.student_id = sh_study_infos.student_id AND sh_student_statuses.biller_id = sh_study_infos.biller_id AND sh_student_statuses.academic_year = ".$academic_year."", "LEFT");
			$this->db->where("IFNULL(".$this->db->dbprefix('sh_student_statuses').".id, 0) = ", 0);
			$this->db->where("(".$this->db->dbprefix('sh_study_infos').".academic_year + 1) >=",$academic_year);
		} else {
			$this->db->group_start();
			$this->db->where("sh_study_infos.status", "active");
			$this->db->or_where("sh_study_infos.status", "assign");
			$this->db->group_end();
			if($academic_year){
				$this->db->where("sh_study_infos.academic_year", $academic_year);
			}
		}
        $this->db->select('
							sh_students.id,
							sh_students.number,
							sh_students.code,
							sh_students.gender,
							sh_students.lastname,
							sh_students.firstname,
							sh_students.lastname_other,
							sh_students.firstname_other,
							sh_study_infos.id as study_info_id,
							sh_study_infos.class_id,
							sh_study_infos.class as class_name,
							sh_study_infos.academic_year
						')
			->join("sh_students","sh_study_infos.student_id = sh_students.id", "inner")
			->where("
				(
					lastname LIKE '%" . $term . "%' OR 
					firstname LIKE '%" . $term . "%' OR 
					lastname_other LIKE '%" . $term . "%' OR 
					firstname_other LIKE '%" . $term . "%' OR 
					number LIKE '%" . $term . "%' OR
					code LIKE '%" . $term . "%' OR
					CONCAT(lastname,' ', firstname,' ', ' (', number, ')') LIKE '%" . $term . "%' OR 
					CONCAT(lastname_other, ' ', firstname_other, ' ', ' (', number, ')') LIKE '%" . $term . "%' OR
					CONCAT(lastname, ' ', firstname) LIKE '%" . $term . "%' OR
					CONCAT(lastname_other, ' ', firstname_other) LIKE '%" . $term . "%' OR
					CONCAT(firstname, ' ', lastname) LIKE '%" . $term . "%' OR
					CONCAT(firstname_other, ' ', lastname_other) LIKE '%" . $term . "%'
				)
			")
            ->group_by('sh_students.id')->limit(10);
        $q = $this->db->get('sh_study_infos');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	public function getStudyInfo($student_id = false, $academic_year = false, $biller_id = false, $program_id = false)
	{
		if($student_id){
			$this->db->where("sh_study_infos.student_id",$student_id);
		}
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		if($biller_id){
			$this->db->where("sh_study_infos.biller_id",$biller_id);
		}
		if($program_id){
			$this->db->where("sh_study_infos.program_id",$program_id);
		}
		$q = $this->db->get('sh_study_infos');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function addReenrollment($review = false, $data = false){
		if($this->updateStudentStatus($review["id"], $review)){
			$old_study_info = $this->getStudyInfo($data["student_id"],$data["academic_year"],$data["biller_id"],$review["program_id"]);
			if($data && $old_study_info){
				$data["status"] = "active";
				$this->db->update("sh_study_infos",$data,array("student_id"=>$data["student_id"],"academic_year"=>$data["academic_year"],"biller_id"=>$data["biller_id"],"program_id"=>$data["program_id"]));
			}else if($data && $this->db->insert('sh_study_infos',$data)){
				$study_id = $this->db->insert_id();
				$this->autoInvoice($study_id);
			}
			return true;
		}
		return false;
	}
	
	public function getFamilyBlackLists(){
		
		$this->db->where("sh_black_lists.status","active");
		$q = $this->db->get("sh_black_lists");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data["Father"][$row->father] = $row;
				$data["Mother"][$row->mother] = $row;
				$data["Guardian"][$row->guardian] = $row;
            }
            return $data;
        }
        return false;
		
		
		$this->db->select("sh_student_families.relationship,sh_student_families.full_name");
		$this->db->where("(".$this->db->dbprefix('sh_student_statuses').".status = 'black_list' OR ".$this->db->dbprefix('sh_student_statuses').".review_status = 'black_list')");
		$this->db->join("sh_students","sh_students.id = sh_student_statuses.student_id","INNER");
		$this->db->join("sh_student_families","sh_student_families.family_id = sh_students.family_id","INNER");
		$q = $this->db->get("sh_student_statuses");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[$row->relationship][$row->full_name] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function getStudentStatusGraduate($biller_id = false,$academic_year = false,$program_id = false,$grade_id = false, $status= false)
    {
		$limit = $this->Settings->rows_per_page;
		if($biller_id){
			$this->db->where("sh_study_infos.biller_id",$biller_id);
		}
		if($program_id){
			$this->db->where("sh_study_infos.program_id",$program_id);
		}
		if($grade_id){
			$this->db->where("sh_study_infos.grade_id",$grade_id);
		}
		if($academic_year){
			$this->db->where("sh_study_infos.academic_year",$academic_year);
		}
		$this->db->where("sh_study_infos.status","active");
        $this->db->select('
							sh_students.id,
							sh_students.number,
							sh_students.code,
							sh_students.gender,
							sh_students.lastname,
							sh_students.firstname,
							sh_students.lastname_other,
							sh_students.firstname_other,
							sh_study_infos.class_id,
							sh_study_infos.class as class_name,
							sh_study_infos.academic_year
						')
			->join("sh_students","sh_study_infos.student_id = sh_students.id","inner");
		$q = $this->db->get('sh_study_infos');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
	
	public function getBlackListByID($id = false){
		$this->db->select("sh_black_lists.*,
							IF(".$this->db->dbprefix("sh_students").".id > 0, CONCAT(".$this->db->dbprefix("sh_students").".lastname,' ',".$this->db->dbprefix("sh_students").".firstname), ".$this->db->dbprefix("sh_black_lists").".student_name) as student_name,
							IF(".$this->db->dbprefix("sh_students").".id > 0, CONCAT(".$this->db->dbprefix("sh_students").".lastname_other,' ',".$this->db->dbprefix("sh_students").".firstname_other), ".$this->db->dbprefix("sh_black_lists").".student_name_latin) as student_name_latin
						");
		$this->db->join("sh_students","sh_students.id = sh_black_lists.student_id","left");
		$q = $this->db->get_where("sh_black_lists",array("sh_black_lists.id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function addBlackList($data = false){
		if($data && $this->db->insert("sh_black_lists",$data)){
			return true;
		}
		return false;
	}
	public function updateBlackList($id = false, $data = false){
		if($id && $this->db->update("sh_black_lists",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function deleteBlackList($id = false){
		if($id && $this->db->delete("sh_black_lists",array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	public function getBlackListByStudentID($student_id = false){
		$q = $this->db->get_where("sh_black_lists",array("student_id"=>$student_id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function getGradeStudentByID($id = false){
		$q = $this->db->get_where("sh_grade_students", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addGradeStudent($data = false){
		if($this->db->insert("sh_grade_students",$data)){
			return true;
		}
		return false;
	}
	
	public function updateGradeStudent($id = false, $data = false){
		if($id && $data && $this->db->update('sh_grade_students',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteGradeStudent($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_grade_students')){
			return true;
		}
		return false;
	}
	public function getSkillByID($id = false)
	{
		$q = $this->db->get_where("sh_skills", array("sh_skills.id" => $id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getSkillByCode($code)
	{
		$q = $this->db->get_where("sh_skills", array("sh_skills.code" => $code));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function addSkill($data = false)
	{
		if($data && $this->db->insert("sh_skills",$data)){
			return true;
		}
		return false;
	}
	public function updateSkill($id = false, $data = false){
		if($id && $this->db->update("sh_skills",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function deleteSkill($id = false){
		if($id && $this->db->delete("sh_skills",array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function addCollege($data = false){
		if($data && $this->db->insert("sh_colleges",$data)){
			return true;
		}
		return false;
	}
	public function getCollegeByID($id = false){
		$q = $this->db->get_where("sh_colleges", array("sh_colleges.id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function updateCollege($id = false, $data = false){
		if($id && $this->db->update("sh_colleges",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function deleteCollege($id = false){
		if($id && $this->db->delete("sh_colleges", array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function getGradeTestingByID($id = false){
		$q = $this->db->get_where("sh_grade_testings", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addGradeTesting($data = false){
		if($this->db->insert("sh_grade_testings",$data)){
			return true;
		}
		return false;
	}
	
	public function updateGradeTesting($id = false, $data = false){
		if($id && $data && $this->db->update('sh_grade_testings',$data,array('id'=>$id))){
			return true;
		}
		return false;
	}
	public function deleteGradeTesting($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_grade_testings')){
			return true;
		}
		return false;
	}

	public function getTestingGrades($academic_year = false,$biller_id = false,$group_id = false,$start_date = false,$end_date = false){
		if ($academic_year) {
			$this->db->where('sh_testings.academic_year', $academic_year);
		}
		if ($biller_id) {
			$this->db->where('sh_testings.biller_id', $biller_id);
		}
		if ($start_date) {
			$this->db->where('sh_testings.date >=', $start_date);
		}
		if ($end_date) {
			$this->db->where('sh_testings.date <=', $end_date);
		}
		if ($group_id) {
			$this->db->where('sh_testings.group_id', $group_id);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->db->where('sh_testings.biller_id', $this->session->userdata('biller_id'));
		}
		$this->db->select("
							sh_grades.id,
							sh_grades.name
						", FALSE)
				->join('sh_grades','sh_grades.id = sh_testings.testing_grade_id','INNER')
				->group_by("sh_testings.testing_grade_id");
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	
	public function getIndexTestings($academic_year = false,$biller_id = false,$group_id = false,$start_date = false,$end_date = false,$testing_date = false){
		if ($academic_year) {
			$this->db->where('sh_testings.academic_year', $academic_year);
		}
		if ($biller_id) {
			$this->db->where('sh_testings.biller_id', $biller_id);
		}
		if ($start_date) {
			$this->db->where('sh_testings.date >=', $start_date);
		}
		if ($end_date) {
			$this->db->where('sh_testings.date <=', $end_date);
		}
		if ($testing_date) {
			$this->db->where('sh_testing_groups.testing_date', $testing_date);
		}
		if ($group_id) {
			$this->db->where('sh_testings.group_id', $group_id);
		}
		if (!$this->Owner && !$this->Admin && $this->session->userdata('biller_id')) { 
			$this->db->where('sh_testings.biller_id', $this->session->userdata('biller_id'));
		}
		$this->db->select("sh_testings.*,
							sh_testing_groups.testing_date,
							IFNULL(".$this->db->dbprefix("sh_testings").".result_status,'pending') as result_status
						", FALSE)
				->join('sh_testing_groups','sh_testing_groups.id = sh_testings.group_id','left')
				->join('sh_grades','sh_grades.id = sh_testings.testing_grade_id','inner')
				->group_by("sh_testings.id");
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->testing_grade_id][] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexGradeTestings(){
		$this->db->select("sh_grade_testings.*,sh_skills.name as skill_name,sh_grade_testings.grade_id");
		$this->db->join("sh_skills","sh_skills.id = sh_grade_testings.skill_id","inner");
		$this->db->order_by("sh_skills.order_by");
		$q = $this->db->get("sh_grade_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->grade_id][$row->skill_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getTestingResultByID($id = false){
		$q = $this->db->get_where("sh_testing_results",array("sh_testing_results.id"=>$id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	
	public function addTestingResult($data = false){
		if($data && $this->db->insert("sh_testing_results",$data)){
			return true;
		}
		return false;
	}
	
	public function updateTestingResult($id = false, $data = false){
		if($id && $this->db->update("sh_testing_results",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	public function deleteTestingResult($id = false){
		if($id && $this->db->delete("sh_testing_results",array("id"=>$id))){
			return true;
		}
		return false;
	}
	
	public function getTestingResults(){
		$q = $this->db->get("sh_testing_results");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function getTestingStudents($student_id = false, $result_status = false){
		$this->db->where("IFNULL(student_id,0)",$student_id);
		if($result_status){
			$this->db->where("result_status",$result_status);
		}
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
	}
	
	public function getIndexTestingStudents($biller = false,$program = false,$grade = false){
		if($biller){
			$this->db->where("sh_testings.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_testings.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_testings.testing_grade_id",$grade);
		}
		$this->db->group_by("sh_testings.academic_year");
		$this->db->select("sh_testings.academic_year,
							count(".$this->db->dbprefix('sh_testings').".id) as total_student,
							sum(IF(".$this->db->dbprefix('sh_testings').".result_status = 'accepted',1,0)) as accepted_student,
							sum(IF(".$this->db->dbprefix('sh_testings').".result_status = 'rejected',1,0)) as rejected_student
						");
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->academic_year] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexAcceptedStudents($academic_year = false, $biller = false,$program = false,$grade = false){
		if($academic_year){
			$this->db->where("sh_testings.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_testings.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_testings.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_testings.testing_grade_id",$grade);
		}
		$this->db->select("
							sh_testings.biller_id,
							sh_testings.testing_grade_id,
							
							count(".$this->db->dbprefix('sh_testings').".id) as total_student,
							sum(IF(".$this->db->dbprefix('sh_testings').".result_status = 'accepted',1,0)) as accepted_student,
							sum(IF(".$this->db->dbprefix('sh_testings').".result_status = 'rejected',1,0)) as rejected_student
						");
		$this->db->group_by("sh_testings.biller_id,sh_testings.testing_grade_id");
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->biller_id][$row->testing_grade_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}
	
	public function getIndexTotalTestings($academic_year = false, $biller = false,$program = false,$grade = false){
		if($academic_year){
			$this->db->where("sh_testings.academic_year",$academic_year);
		}
		if($biller){
			$this->db->where("sh_testings.biller_id",$biller);
		}
		if($program){
			$this->db->where("sh_testings.program_id",$program);
		}
		if($grade){
			$this->db->where("sh_testings.testing_grade_id",$grade);
		}
		$this->db->group_by("sh_testings.testing_grade_id");
		$this->db->select("sh_testings.testing_grade_id,
							sh_grade_students.number_student,
							count(".$this->db->dbprefix('sh_testings').".id) as testing_student,
							sum(IF(".$this->db->dbprefix('sh_testings').".result_status = 'accepted',1,0)) as accepted_student,
							sum(IF(".$this->db->dbprefix('sh_testings').".result_status = 'rejected',1,0)) as rejected_student
						");
		$this->db->join("sh_grade_students","sh_grade_students.grade_id = sh_testings.testing_grade_id AND sh_grade_students.academic_year = sh_testings.academic_year","left");				
		$q = $this->db->get("sh_testings");
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[$row->testing_grade_id] = $row;
            }
            return $data;
        }
        return FALSE;
	}

	public function getStudentAdmisssionName($term = false, $biller_id = false, $academic_year = false, $program_id = false, $skill_id = false, $grade_id = false, $semester_id = false, $timeshift = false)
    {
		$limit = $this->Settings->rows_per_page;
		// $this->db->where('is_admission', 1);
        $this->db->select('
							sh_students.id,
							sh_students.number,
							sh_students.code,
							sh_students.gender,
							sh_students.lastname,
							sh_students.firstname,
							sh_students.lastname_other,
							sh_students.firstname_other
						')
			->where("
					(
						{$this->db->dbprefix('sh_students')}.lastname LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.firstname LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.lastname_other LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.firstname_other LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.number LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.code LIKE '%" . $term . "%' OR 
						CONCAT(firstname, ' ', lastname) LIKE '%" . $term . "%' OR
						CONCAT(lastname_other, ' ', firstname_other) LIKE '%" . $term . "%' OR
						CONCAT(firstname_other, ' ', lastname_other) LIKE '%" . $term . "%' OR
						CONCAT(lastname, ' ', firstname, ' ', ' (', number, ')') LIKE '%" . $term . "%'
					) 
			");

		$this->db->group_start();
		$this->db->where('sh_students.approval_status', 'approved');
		$this->db->or_where('sh_students.approval_status', 'accepted');
		$this->db->group_end();

		if($biller_id){
			$this->db->where('biller_id', $biller_id);
		}
		if($academic_year){
			$this->db->where('academic_year', $academic_year);
		}
		if($program_id){
			$this->db->where('program_id', $program_id);
		}
		if($skill_id){
			$this->db->where('skill_id', $skill_id);
		}
		if($timeshift){
			$this->db->where('timeshift_id', $timeshift);
		} else {
			// $this->db->where('timeshift_id', null);
		}

        $this->db->group_by('sh_students.id')->limit($limit);
        $q = $this->db->get('sh_students');
		if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
				$data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getSubjectsBySkill($skill_id)
    {
    	$q = $this->db->get_where('sh_subjects', ['skill_id' => $skill_id]);
    	if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
    }

    public function getSections($program_id, $skill_id, $grade_id)
    {
    	$q = $this->db->get_where('sh_sections', ['program_id' => $program_id, 'skill_id' => $skill_id, 'grade_id' => $grade_id]);
    	if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
    }

	public function getSubjectByclass_id_academic_year_section_id_day($class_id, $academic_year, $section_id, $timestamp)
	{
		$this->db->select('sh_table_times.*, sh_table_times.id as timetable_id, sh_subjects.id as subject_id, sh_subjects.name as subject');
		$this->db->from('sh_table_times');
		$this->db->join('sh_subjects','sh_subjects.id = sh_table_times.subject_id','left');
		if ($class_id) {
			$this->db->where("sh_table_times.class_id", $class_id);
		}
		if ($academic_year) {
			$this->db->where("sh_table_times.academic_year", $academic_year);
		}
		if ($section_id) {
			$this->db->where("sh_table_times.section_id", $section_id);
		}
		if ($timestamp) {
			$this->db->where("{$this->db->dbprefix('sh_table_times')}.day", $timestamp);
		}
		$q = $this->db->get();
		if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
	}

	public function getClassesBy_academic_biller_program_skill_grade_timeshift($academic_year = null, $biller_id, $program_id, $skill_id, $grade_id, $timeshift_id = null)
    {
		$this->db->select("sh_classes.*");
		$this->db->from("sh_classes");
		$this->db->join("sh_table_times", "bpas_sh_table_times.class_id=sh_classes.id", "left");
		if ($academic_year) {
			$this->db->where("sh_table_times.academic_year", $academic_year);
		}
		if ($biller_id) {
			$this->db->where("sh_classes.biller_id", $biller_id);
		}
		if ($program_id) {
			$this->db->where("sh_classes.program_id", $program_id);
		}
		if ($skill_id) {
			$this->db->where("sh_classes.skill_id", $skill_id);
		}
		if ($grade_id) {
			$this->db->where("sh_classes.grade_id", $grade_id);
		}
		if ($timeshift_id) {
			$this->db->where("sh_classes.timeshift_id", $timeshift_id);
		}
		$this->db->group_by("sh_classes.id");
    	$q = $this->db->get();
    	if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
    }
	public function addDocumentforms($data, $sub_data = null)
	{
		if (!empty($data)) { 
			foreach ($data as $study_info) {
				$student_id = $study_info['student_id'];
				$this->db->insert('sh_document_forms', $study_info);
				$df_id = $this->db->insert_id();
				if (!empty($sub_data)) {
					foreach ($sub_data as  $value) {
						$value['document_id'] = $df_id;
						$this->db->insert('sh_document_students', $value);
					}
					
				}
			}
			return true;
		}
		return false;
	}
	public function assignStudent($data)
	{
		if (!empty($data)) {
			foreach ($data as $study_info) {
				$student_id = $study_info['student_id'];
				$arr = [
					'student_id'    => $student_id,
					'academic_year' => $study_info['academic_year'],
					'program_id'    => $study_info['program_id'],
					'skill_id'      => $study_info['skill_id'],
					'grade_id'      => $study_info['grade_id'],
					'section_id'    => $study_info['section_id']
				];
				if (!$this->duplicate_assign_student(null, $arr)) {
					if($this->db->insert('sh_study_infos', $study_info)){
						$study_id = $this->db->insert_id();
						$this->db->update("sh_students", ['is_admission' => 0], ['id' => $student_id]);
						$this->autoInvoice($study_id);
					}
				} else {
					$student = $this->getStudentByID($student_id);
					$this->session->set_flashdata('error', 'This student ' . $student->firstname . ' ' . $student->lastname . ' (' . $student->number . ') is already assigned!');
					return;
				}
			}
			return true;
		}
		return false;
	}

	public function updateAssignStudent($id, $data) 
	{
		if ($id && !empty($data)) {
			$arr = [
				'student_id'    => $data['student_id'],
				'academic_year' => $data['academic_year'],
				'program_id'    => $data['program_id'],
				'skill_id'      => $data['skill_id'],
				'grade_id'      => $data['grade_id'],
				'section_id'    => $data['section_id']
			];
			if (!$this->duplicate_assign_student($id, $arr)) {
				$this->db->update("sh_study_infos", $data, ['id' => $id]);
				return true;
			}
			$this->session->set_flashdata('error', 'Duplicate student assign!');
			return;
		}
		return false;
	}

	public function deleteAssignStudentByStudyInfoID($id)
	{
		if (!empty($id)) {
			if ($this->db->delete('sh_study_infos', ['id' => $id])) {
				return true;
			}
			return false;
		}
		return false;
	}

	public function deleteAssignStudent($id)
	{
		if (!empty($id)) {
			$this->db->delete('sh_study_infos', ['id' => $id]);
		}
	}

	public function duplicate_assign_student($id, $data)
	{
		$this->db->where($data);
		if ($id) {
			$this->db->where("id !=", $id);
		}
		$q = $this->db->get('sh_study_infos');
		if ($q->num_rows() > 0){
			return true;
		}
		return false;
	}

	public function getFeesMasterByID($id)
	{
		$this->db->select("
				{$this->db->dbprefix('sh_grade_fees')}.*,
				{$this->db->dbprefix('sh_skills')}.name as skill,
				{$this->db->dbprefix('sh_grades')}.name as grade,
				{$this->db->dbprefix('sh_sections')}.name as section,
				{$this->db->dbprefix('categories')}.name as fee_type,
				{$this->db->dbprefix('products')}.code as product_code,
				{$this->db->dbprefix('products')}.name as product_name,
				CONCAT({$this->db->dbprefix('products')}.code, ' - ', {$this->db->dbprefix('products')}.name) as product,
				{$this->db->dbprefix('products')}.price as fee_price
			");
		$this->db->from('sh_grade_fees');
		$this->db->join('products', 'sh_grade_fees.product_id = products.id', 'left');
		$this->db->join('categories', 'products.category_id = categories.id', 'left');
		$this->db->join('sh_skills', 'sh_grade_fees.skill_id = sh_skills.id', 'left');
		$this->db->join('sh_grades', 'sh_grade_fees.grade_id = sh_grades.id', 'left');
		$this->db->join('sh_sections', 'sh_grade_fees.section_id = sh_sections.id', 'left');
		$this->db->where('sh_grade_fees.id', $id);
		$this->db->limit(1);

		$q = $this->db->get();
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

	public function addScholarship($data = array())
	{
		if($data && $this->db->insert('sh_scholarships',$data)){
			return true;
		}
		return false;
	}

	public function updateScholarship($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id", $id)->update('sh_scholarships', $data)){
			return true;
		}
		return false;
	}

	public function deleteScholarshipByID($id = false)
	{
		if($id && $this->db->where("id", $id)->delete('sh_scholarships')){
			return true;
		}
		return false;
	}

	public function getScholarshipByID($id)
	{
		$this->db->select(' sh_scholarships.*, price_groups.name as price_group ');
		$this->db->join('price_groups', 'price_groups.id=sh_scholarships.price_group_id', 'left');
		$this->db->where('sh_scholarships.id', $id);
		$this->db->limit(1);
		$q = $this->db->get('sh_scholarships');
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}
	public function getScholarships($academic_year = null) 
	{
		$this->db->select(" {$this->db->dbprefix('sh_scholarships')}.* ");
		if ($academic_year) {
			$this->db->where('sh_scholarships.academic_year', $academic_year);
		}
		$q = $this->db->get('sh_scholarships');
		if ($q->num_rows() > 0) {
			foreach (($q->result()) as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getScholarshipByName($name, $academic_year)
	{
		$this->db->select(' sh_scholarships.*');
		$this->db->where('sh_scholarships.name', "$name");
		$this->db->where('sh_scholarships.academic_year', "$academic_year");
		$q = $this->db->get('sh_scholarships');
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

	public function getDoc_Ask_Permission($id = false)
	{	
		$this->db->where("sh_document_forms.id",$id);
		$this->db->select("sh_document_forms.*,CONCAT(".$this->db->dbprefix("sh_students").".lastname,' ',".$this->db->dbprefix("sh_students").".firstname)
		as fullname,".$this->db->dbprefix("sh_study_infos").".timeshift_id as timeshift_id,".$this->db->dbprefix("sh_programs").".name as program_name, gender , nationality,CONCAT(".$this->db->dbprefix("sh_students").".lastname_other,' ',".$this->db->dbprefix("sh_students").".firstname_other) as latang, sh_colleges.name as college_name, sh_skills.name as skillname, sh_study_infos.grade as grade , sh_sections.name as section_name, sh_study_infos.generation as generation, phone, sh_document_forms.description as description, cf_start_date, cf_end_date, day , sh_study_infos.batch as batch");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = sh_document_forms.student_id","left");
		$this->db->join("sh_students","sh_students.id = sh_document_forms.student_id","left");
		$this->db->join("sh_skills","sh_skills.id = sh_study_infos.skill_id","left");
		$this->db->join("sh_colleges","sh_colleges.id = sh_skills.college_id","left");
		$this->db->join("sh_programs","sh_programs.id = sh_study_infos.program_id","left");
		$this->db->join("sh_sections","sh_study_infos.section_id = sh_sections.id","left");
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getaddtimeItems($addtime_id = false)
	{	
		$this->db->where("sh_document_forms.id",$addtime_id);	
		$this->db->select("sh_document_forms.*,CONCAT(".$this->db->dbprefix("sh_teachers").".lastname,' ',".$this->db->dbprefix("sh_teachers").".firstname)
		as fullname, gender , sh_skills.name as name_room , sh_skills.name as nameskill,cf_start_date,cf_start_time, cf_end_time, sh_document_forms.description as description,".$this->db->dbprefix("sh_grades").".name as grade_name,");
		$this->db->join("sh_teachers","sh_teachers.id = sh_document_forms.student_id","left");
		$this->db->join("sh_skills","sh_skills.id = sh_document_forms.skill","left");
		$this->db->join("sh_grades","sh_grades.id = sh_document_forms.grade","left");		
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getChangetimeItems($change_id = false)
	{	
		$this->db->where("sh_document_forms.id", $change_id);	
		$this->db->select("sh_document_forms.*,CONCAT(".$this->db->dbprefix("sh_students").".lastname,' ',".$this->db->dbprefix("sh_students").".firstname)
		as fullname,CONCAT(".$this->db->dbprefix("sh_students").".lastname_other,' ',".$this->db->dbprefix("sh_students").".firstname_other) as latang,gender,sh_study_infos.generation as generation,".$this->db->dbprefix("sh_skills").".name as nameskill, a.name as old_time,b.name as new_time, ".$this->db->dbprefix("sh_document_forms").".description as description,".$this->db->dbprefix("sh_colleges").".name as college_name,
		".$this->db->dbprefix("sh_study_infos").".grade as grade,".$this->db->dbprefix("sh_students").".phone as phone,".$this->db->dbprefix("sh_study_infos").".batch as batch,sh_sections.name as section_name,".$this->db->dbprefix("sh_students").".nationality as nationality");

		// $this->db->join("sh_teachers","sh_teachers.id = sh_document_forms.student_id","left");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = sh_document_forms.student_id","left");
		$this->db->join("sh_skills","sh_skills.id = sh_study_infos.skill_id","left");
		$this->db->join("custom_field a","a.id = sh_document_forms.old_time","left");
		$this->db->join("sh_colleges","sh_colleges.id = sh_skills.college_id","left");
		$this->db->join("custom_field b","b.id = sh_document_forms.new_time","left");
		$this->db->join("sh_sections","sh_study_infos.section_id = sh_sections.id","left");
		$this->db->join("sh_students","sh_students.id = sh_document_forms.student_id","left");
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getdelaystudyItems($delay_id = false)
	{	
		$this->db->where("sh_document_forms.id",$delay_id);	
		$this->db->select("sh_document_forms.*,CONCAT(".$this->db->dbprefix("sh_students").".lastname,' ',".$this->db->dbprefix("sh_students").".firstname)
		as fullname,CONCAT(".$this->db->dbprefix("sh_students").".lastname_other,' ',".$this->db->dbprefix("sh_students").".firstname_other) as latang,gender,sh_study_infos.generation as generation,".$this->db->dbprefix("sh_skills").".name as nameskill, a.name as old_time,b.name as new_time, ".$this->db->dbprefix("sh_document_forms").".description as description,".$this->db->dbprefix("sh_colleges").".name as college_name,
		".$this->db->dbprefix("sh_study_infos").".grade as grade,".$this->db->dbprefix("sh_students").".phone as phone,".$this->db->dbprefix("sh_study_infos").".batch as batch,sh_sections.name as section_name,".$this->db->dbprefix("sh_students").".nationality as nationality,cf_start_date, cf_end_date,");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = sh_document_forms.student_id","left");
		$this->db->join("sh_skills","sh_skills.id = sh_study_infos.skill_id","left");
		$this->db->join("custom_field a","a.id = sh_document_forms.old_time","left");
		$this->db->join("sh_colleges","sh_colleges.id = sh_skills.college_id","left");
		$this->db->join("custom_field b","b.id = sh_document_forms.new_time","left");
		$this->db->join("sh_sections","sh_study_infos.section_id = sh_sections.id","left");
		$this->db->join("sh_students","sh_students.id = sh_document_forms.student_id","left");		
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getStopItems($stop_id = false)
	{
		$this->db->where("sh_document_forms.id",$stop_id);
		$this->db->select("sh_document_forms.*,CONCAT(".$this->db->dbprefix("sh_students").".lastname,' ',".$this->db->dbprefix("sh_students").".firstname)
		as fullname,CONCAT(".$this->db->dbprefix("sh_students").".lastname_other,' ',".$this->db->dbprefix("sh_students").".firstname_other) as latang,gender,sh_study_infos.generation as generation,".$this->db->dbprefix("sh_skills").".name as nameskill, a.name as old_time,b.name as new_time, ".$this->db->dbprefix("sh_document_forms").".description as description,".$this->db->dbprefix("sh_colleges").".name as college_name,
		".$this->db->dbprefix("sh_study_infos").".grade as grade,".$this->db->dbprefix("sh_students").".phone as phone,".$this->db->dbprefix("sh_study_infos").".batch as batch,sh_sections.name as section_name,".$this->db->dbprefix("sh_students").".nationality as nationality,cf_start_date, cf_end_date,".$this->db->dbprefix("sh_document_forms").".day as day,");

		$this->db->join("sh_study_infos","sh_study_infos.student_id = sh_document_forms.student_id","left");
		$this->db->join("sh_skills","sh_skills.id = sh_study_infos.skill_id","left");
		$this->db->join("custom_field a","a.id = sh_document_forms.old_time","left");
		$this->db->join("sh_colleges","sh_colleges.id = sh_skills.college_id","left");
		$this->db->join("custom_field b","b.id = sh_document_forms.new_time","left");
		$this->db->join("sh_sections","sh_study_infos.section_id = sh_sections.id","left");
		$this->db->join("sh_students","sh_students.id = sh_document_forms.student_id","left");	
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getStopstudyItems($stopstudy_id = false)
	{	
		$this->db->where("sh_document_forms.id",$stopstudy_id);	
		$this->db->select("sh_document_forms.*,CONCAT(".$this->db->dbprefix("sh_students").".lastname,' ',".$this->db->dbprefix("sh_students").".firstname)
		as fullname,CONCAT(".$this->db->dbprefix("sh_students").".lastname_other,' ',".$this->db->dbprefix("sh_students").".firstname_other) as latang,gender,sh_study_infos.generation as generation,".$this->db->dbprefix("sh_skills").".name as nameskill, a.name as old_time,b.name as new_time, ".$this->db->dbprefix("sh_document_forms").".description as description,".$this->db->dbprefix("sh_colleges").".name as college_name,
		".$this->db->dbprefix("sh_study_infos").".grade as grade,".$this->db->dbprefix("sh_students").".phone as phone,".$this->db->dbprefix("sh_study_infos").".batch as batch,sh_sections.name as section_name,".$this->db->dbprefix("sh_students").".nationality as nationality,cf_start_date, cf_end_date,".$this->db->dbprefix("sh_document_forms").".day as day,");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = sh_document_forms.student_id","left");
		$this->db->join("sh_skills","sh_skills.id = sh_study_infos.skill_id","left");
		$this->db->join("custom_field a","a.id = sh_document_forms.old_time","left");
		$this->db->join("sh_colleges","sh_colleges.id = sh_skills.college_id","left");
		$this->db->join("custom_field b","b.id = sh_document_forms.new_time","left");
		$this->db->join("sh_sections","sh_study_infos.section_id = sh_sections.id","left");
		$this->db->join("sh_students","sh_students.id = sh_document_forms.student_id","left");
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getForcetificateItems($for_ct_id = false)
	{
		$this->db->where("sh_document_forms.id",$for_ct_id);
		$this->db->select("sh_document_forms.*,".$this->db->dbprefix("sh_document_forms").".description as description,");
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getRequestcetificateItems($for_ct_id = false)
	{
		$this->db->where("sh_document_forms.id",$for_ct_id);		
		$this->db->select("sh_document_forms.*,".$this->db->dbprefix("sh_document_forms").".description as description,".$this->db->dbprefix("sh_study_infos").".grade as grade,");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = sh_document_forms.student_id","left");
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getrequestcetificatefyItems($for_ct_id = false)
	{
		$this->db->where("sh_document_forms.id",$for_ct_id);
		$this->db->select("sh_document_forms.*,".$this->db->dbprefix("sh_document_forms").".description as description,");
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}

	public function getTranscriptItems($for_ct_id = false)
	{	
		$this->db->where("sh_document_forms.id",$for_ct_id);
		$this->db->select("sh_document_forms.*,".$this->db->dbprefix("sh_document_forms").".description as description,".$this->db->dbprefix("sh_study_infos").".grade as grade,");
		$this->db->join("sh_study_infos","sh_study_infos.student_id = sh_document_forms.student_id","left");
		$q = $this->db->get("sh_document_forms");
		if($q->num_rows() > 0){
 			return $q->row();
		}
      	return FALSE;
	}
	public function getDepartmentsByBilller($biller_id = false){
		if($biller_id){
			$this->db->where("hr_departments.biller_id",$biller_id);
		}
		$q = $this->db->get("hr_departments");
		if ($q->num_rows() > 0) {
            foreach ($q->result() as $row) {
                $data[] = $row;
            }
            return $data;
        }
		return false;
	}
	
	public function get_class_attendance_teacher($biller_id, $att_date = false, $teacher_id = false, $academic_year = false)
    {
		$this->db->select("sh_table_times.*,
			sh_subjects.name as subject,
			sh_rooms.name as room,
			sh_classes.name as class,
			sh_teachers.code,sh_teachers.lastname,sh_teachers.firstname,sh_teachers.gender");
		$this->db->join("sh_classes", "sh_classes.id=sh_table_times.class_id", "left");
		$this->db->join("sh_teachers", "sh_teachers.id=sh_table_times.teacher_id", "left");
		$this->db->join("sh_rooms", "sh_rooms.id=sh_table_times.room_id", "left");
		$this->db->join("sh_subjects", "sh_subjects.id=sh_table_times.subject_id", "left");
		if ($biller_id) {
			$this->db->where("sh_teachers.biller_id", $biller_id);
		}
		if ($att_date) {
			$this->db->where("sh_table_times.day", $att_date);
		}
		if ($teacher_id) {
			$this->db->where("sh_table_times.teacher_id", $teacher_id);
		}
		if ($academic_year) {
			$this->db->where('sh_table_times.academic_year', $academic_year);
		}
		$this->db->from("sh_table_times");
    	$q = $this->db->get();
    	if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
    }

    public function getTeachersWorkingInfoByEmployeeID($id = false)
	{
		$q = $this->db->get_where('sh_teacher_working_info', array('employee_id' => $id), 1);
		
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	
	public function addTeacherWorkingInfo($id = false, $data = array())
	{
		$q = $this->getTeachersWorkingInfoByEmployeeID($id);
		if($q){
			if($this->db->where("employee_id",$id)->update('sh_teacher_working_info',$data)){
				return true;
			}
		}else{
			if($this->db->insert('sh_teacher_working_info',$data)){
				return true;
			}
		}
		
		return false;
	}
	public function getEmployeeTypes()
	{
		$q = $this->db->get('hr_employees_types');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function deleteDocumentForm($id = false){
		if($id && $this->db->delete("sh_document_forms",array("id"=>$id))){
			return true;
		}
		return false;
	}
	public function getPackageByID($id = false)
    {
        $q = $this->db->get_where('customer_package', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    public function addServicePackage($data = false){
		if($data && $this->db->insert('service_package',$data)){
			return true;
		}
		return false;
	}
	public function deleteAssginServiceByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('service_package')){
			return true;
		}
		return false;
	}
	public function getServicePackageByID($id = false)
	{
		$q = $this->db->get_where('service_package', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateAssignService($id = false, $data = array()){
		if($id && $data && $this->db->where("id",$id)->update('service_package', $data)){
			return true;
		}
		return false;
	}
	public function get_package_by_customer($biller_id,$customer_id = false)
    {
		$this->db->select("customer_package.*");
		$this->db->join("companies", "companies.service_package=customer_package.id", "left");
		if ($customer_id) {
			$this->db->where("companies.id", $customer_id);
		}
		$this->db->where("companies.group_name",'customer');
		
		$this->db->from("customer_package");
    	$q = $this->db->get();
    	if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
    }

    public function generate_membership_invoice($data = [], $items = [], $payment = [], $si_return = [], $accTrans = array(), $accTranPayments = array(), $payment_status = null, $commission_product = null)
    {  
        $this->db->trans_start();
        if ($this->db->insert('sales', $data)) {
            $sale_id = $this->db->insert_id();

            //=========Add Accounting =========//
            if($accTrans){
                foreach($accTrans as $accTran){
                    $accTran['tran_no'] = $sale_id;
                    $this->db->insert('gl_trans', $accTran);
                }
            }
            if ((isset($payment['amount']) && $payment['amount'] == '')) {
                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id'] = $sale_id;
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
            }
            //=========End Accounting =========//
            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            } elseif ($this->site->getReference('st') == $data['reference_no']) {
                $this->site->updateReference('st');
            }
            
            foreach ($items as $item) {
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();

                if ($this->Settings->product_option && isset($item['max_serial'])) {
                    $this->db->update('product_options', ['start_no' => $item['serial_no'], 'last_no' => $item['max_serial']], ['option_id' => $item['option_id']]);
                }
                $aprroved['purchase_request_id'] = $sale_id;
                $this->db->insert('approved', $aprroved);
                if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment' && empty($si_return)) {
                    $item_costs = $this->site->item_costing($item);
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date']) || isset($item_cost['pi_overselling'])) {
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id']      = $sale_id;
                            $item_cost['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                            if (!isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                                $ic['sale_item_id'] = $sale_item_id;
                                $ic['sale_id']      = $sale_id;
                                $ic['date']         = date('Y-m-d H:i:s', strtotime($data['date']));
                                if (!isset($ic['pi_overselling'])) {
                                    $this->db->insert('costing', $ic);
                                }
                            }
                        }
                    }
                }
            }

            if ($data['sale_status'] == 'completed' || $data['sale_status'] == 'consignment') {
                $this->site->syncPurchaseItems($cost);
            }

            if (!empty($si_return)) {
            
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);

                    if ($product->type == 'combo' && $product->module_type != "property") {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            $this->UpdateCostingAndPurchaseItem($return_item, $combo_item->id, ($return_item['quantity'] * $combo_item->qty));
                        }
                    } else{
                        $this->UpdateCostingAndPurchaseItem($return_item, $return_item['product_id'], $return_item['quantity'],$return_item['expiry']);
                    }
                }

                $q=$this->db->get_where('sales', ['id' => $data['sale_id']],1);
                if ($q->num_rows() > 0) {
                    $return_sale_total_ = ($q->row()->return_sale_total ? $q->row()->return_sale_total : 0);
                }
                
                $this->db->update('sales', ['return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => ($data['grand_total'] + $return_sale_total_), 'return_id' => $sale_id], ['id' => $data['sale_id']]);

                $customer = $this->site->getCompanyByID($data['customer_id']);

                if(isset($data['saleman_by'])){
                    $staff = $this->site->getUser($data['saleman_by']);
                }

            }
            if(isset($data['saleman_by'])){
                $staff = $this->site->getUser($data['saleman_by']);
            }
               

            if ($data['payment_status'] == 'paid') {
                $this->site->update_property_status($sale_id,'sold');
            }
            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                $payment['sale_id'] = $sale_id;
          
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update(
                            'companies', 
                            [
                                'deposit_amount' => ($customer->deposit_amount - $payment['amount']),
                                'deposit_amount_usd' => ($customer->deposit_amount_usd - $payment['amount_usd']),
                                'deposit_amount_khr' => ($customer->deposit_amount_khr - $payment['amount_khr']),
                                'deposit_amount_thb' => ($customer->deposit_amount_thb - $payment['amount_thb']),
                            ], 
                            ['id' => $customer->id]);
                    }
                    $this->db->insert('payments', $payment);
                
                //=========Add Accounting =========//
                $payment_id = $this->db->insert_id();

                if($accTranPayments){
                    foreach($accTranPayments as $accTranPayment){
                        $accTranPayment['tran_no'] = $sale_id;
                        $accTranPayment['payment_id']= $payment_id;
                        if (empty($accTranPayment['reference_no'])) {
                            $accTranPayment['reference_no'] = $payment['reference_no'];
                        }
                        $this->db->insert('gl_trans', $accTranPayment);
                    }
                }
                 //=========End Accounting =========//

                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                if ($this->site->getReference('pp') == $payment['reference_no']) {
                    $this->site->updateReference('pp');
                }
                $this->site->syncSalePayments($sale_id);
            }
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            log_message('error', 'An errors has been occurred while adding the sale (Add:Sales_model.php)');
        } else {
            return $sale_id;
        }

        return false;
    }
    public function updateAdmissionStatus($id = false, $status = false)
    {
		if($id && $this->db->update("sh_students", array("approval_status" => $status), array("id" => $id))){
			return true;
		}
		return false;
	}

	public function getStudentNames_for_Scholarship($term = false, $biller_id, $academic_year, $limit = false, $study_info_id = false)
    {
		$limit = ($limit ? $limit : $this->Settings->rows_per_page);
		$this->db->select(" 
				{$this->db->dbprefix('sh_students')}.*,  
				{$this->db->dbprefix('sh_students')}.id AS student_id,  
				{$this->db->dbprefix('sh_study_infos')}.id AS id,  
				{$this->db->dbprefix('sh_study_infos')}.program_id,  
				{$this->db->dbprefix('sh_study_infos')}.program, 
				{$this->db->dbprefix('sh_study_infos')}.skill_id,  
				{$this->db->dbprefix('sh_skills')}.name AS skill_name, 
				{$this->db->dbprefix('sh_skills')}.code AS skill_code, 
				{$this->db->dbprefix('sh_study_infos')}.grade_id,  
				{$this->db->dbprefix('sh_study_infos')}.grade,  
				{$this->db->dbprefix('sh_study_infos')}.section_id,  
				{$this->db->dbprefix('sh_sections')}.name AS section,  
				{$this->db->dbprefix('sh_study_infos')}.class_id,  
				{$this->db->dbprefix('sh_study_infos')}.class,  
				COALESCE({$this->db->dbprefix('sh_study_infos')}.batch, '') AS batch,  
				{$this->db->dbprefix('sh_study_infos')}.timeshift_id,  
				{$this->db->dbprefix('custom_field')}.name AS timeshift,
			");
		$this->db->join(
			"sh_study_infos", 
			"sh_study_infos.student_id = sh_students.id AND sh_study_infos.academic_year = {$academic_year} AND sh_study_infos.biller_id = {$biller_id} ", 
			"inner");
		$this->db->join("custom_field", "custom_field.id = sh_study_infos.timeshift_id", "left");
		$this->db->join("sh_skills", "sh_skills.id = sh_study_infos.skill_id", "left");
		$this->db->join("sh_sections", "sh_sections.id = sh_study_infos.section_id", "left");
		$this->db->where("sh_students.status", "active");
		if ($term) {
			$this->db->where(" ( 
					firstname LIKE '%" . $term . "%' OR 
					lastname LIKE '%" . $term . "%' OR 
					CONCAT(firstname, ' ', lastname) LIKE '%" . $term . "%' OR
					CONCAT(lastname, ' ', firstname) LIKE '%" . $term . "%' OR
					CONCAT(firstname_other, ' ', lastname_other) LIKE '%" . $term . "%' OR
					CONCAT(lastname_other, ' ', firstname_other) LIKE '%" . $term . "%' OR
					sh_students.phone LIKE '%" . $term . "%' OR 
					sh_students.email LIKE '%" . $term . "%' OR 
					sh_students.number LIKE '%" . $term . "%' OR 
					sh_students.code LIKE '%" . $term . "%'
				) ");
		}
		if ($study_info_id) {
			$this->db->where('sh_study_infos.id', $study_info_id);
			$this->db->limit(1);
		} else {
			$this->db->group_start();
			$this->db->where("sh_study_infos.scholarship_id", null);
			$this->db->or_where("sh_study_infos.scholarship_id", '');
			$this->db->group_end();
			$this->db->limit($limit);
		}
        $q = $this->db->get('sh_students');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function assignStudentsScholarship($scholarship_id, $data)
    {
    	if ($scholarship_id && !empty($data)) {
    		foreach ($data as $id) {
    			$this->db->update('sh_study_infos', ['scholarship_id' => $scholarship_id], ['id' => $id]);	
    		}
    		return true;
    	}
    	return false;
    }

    public function deleteAssignStudentScholarchip($id)
    {
    	if (!empty($id)) {
    		$this->db->update('sh_study_infos', ['scholarship_id' => null], ['id' => $id]);
    		return true;
    	}
    	return false;
    }

    public function getProductOptions($product_id, $warehouse_id, $all = null)
    {
        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $wpv = "( SELECT option_id, warehouse_id, quantity from {$this->db->dbprefix('warehouses_products_variants')} WHERE product_id = {$product_id}) FWPV";
        $this->db->select('product_variants.id as id, product_variants.name as name, product_variants.price as price, product_variants.quantity as total_quantity, FWPV.quantity as quantity', false)
            ->join($wpv, 'FWPV.option_id=product_variants.id', 'left')
            //->join('warehouses', 'warehouses.id=product_variants.warehouse_id', 'left')
            ->where('product_variants.product_id', $product_id)
            ->group_by('product_variants.id');

        if ((!$this->Settings->overselling || ($this->Settings->overselling && !$warehouse->overselling)) && !$all) {
            $this->db->where('FWPV.warehouse_id', $warehouse_id);
            $this->db->where('FWPV.quantity >', 0);
        }
        $q = $this->db->get('product_variants');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }

    public function getProductOptionByID($id)
    {
        $q = $this->db->get_where('product_variants', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return false;
    }

	public function getStudyInfoByArray($arr)
	{
		$q = $this->db->get_where('sh_study_infos', $arr, 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

	public function getTimeshiftByName($text)
	{
		$q = $this->db->get_where('custom_field', ['name' => $text], 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

	public function getTimeshiftByID($id)
	{
		$q = $this->db->get_where('custom_field', ['id' => $id], 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

	public function getSectionByCode($code)
	{
		$q = $this->db->get_where('sh_sections', ['code' => $code], 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

	public function getClassByCode($code)
	{
		$q = $this->db->get_where('sh_classes', ['code' => $code], 1);
		if ($q->num_rows() > 0) {
			return $q->row();
		}
		return false;
	}

	public function getStudentByClass($class_id = false,$section_id = false, $academic_year = false)
    {
		if($class_id){
			$this->db->join('sh_study_infos','sh_study_infos.student_id = sh_students.id','inner');
			$this->db->where('sh_study_infos.class_id',$class_id);
		} 
		if($section_id){
		 	$this->db->join('sh_sections','sh_sections.id = sh_study_infos.section_id','inner');
			$this->db->where('sh_study_infos.section_id',$section_id);
		}
		if($academic_year){
			$this->db->where('sh_study_infos.academic_year',$academic_year);
		}
		$this->db->where('sh_students.status','active');
        $this->db->select('sh_sections.subject_id as subject,sh_students.*');
			
        $q = $this->db->get('sh_students');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return false;
    }
	
    public function convertJsonScore($json)
    {
    	$score = json_decode($json,true);
		$count= count($score);
		for ($i=0; $i < $count; $i++){ 
			//echo 'subject ='.$score[$i]["subject_id"] .'score ='.$score[$i]["score"] . "<br>";
			$getsub = $this->getSubjectByID($score[$i]["subject_id"]);
			$row =array(
					'subject_id' => $score[$i]["subject_id"],
					'name'      => $getsub->name
				);
			$data[] = $row;	
		}
		return $data;
    }

    public function addExam($data)
    {
    	if($data && $this->db->insert('sh_exams', $data)){
    		$this->site->updateReference('sh_exm');
    		return true;
    	}
    	return false;
    }

    public function updateExam($id, $data)
    {
    	if (!empty($id) && $id != '') {
    		if ($this->db->update('sh_exams', $data, ['id' => $id])) {
    			return true;
    		}
    		return false;
    	}
    	return false;
    }

    public function deleteExam($id)
    {
    	if (!empty($id) && $id != '') {
    		if ($this->db->delete('sh_exams', ['id' => $id])) {
    			return true;
    		}
    		return false;
    	}
    	return false;
    }

    public function getExamByID($id)
    {
    	if (!empty($id) && $id != '') {
    		$this->db->select('sh_exams.id as id, sh_exams.*, companies.company as biller, sh_programs.name as program');
    		$this->db->join('companies', 'companies.id = sh_exams.biller_id', 'left');
    		$this->db->join('sh_programs', 'sh_programs.id = sh_exams.program_id', 'left');
    		$q = $this->db->get_where('sh_exams', ['sh_exams.id' => $id], 1);
    		if ($q->num_rows() > 0) {
    			return $q->row();
    		}
    		return false;
    	}
    	return false;
    }

    public function addExamSchedule($exam_id, $data)
    {
    	if (!empty($exam_id) && !empty($data)) {
    		if ($this->getExamScheduleByExamID($exam_id)) {
    			$this->db->delete('sh_exam_schedules', ['exam_id' => $exam_id]);
    		}
			$this->db->insert_batch('sh_exam_schedules', $data);
			return true;
    	}
    	return false;
    }

    public function getExamScheduleByExamID($id, $row_id = null) 
    {
    	if (!empty($id) && $id != "") {
			$this->db->select("
				{$this->db->dbprefix('sh_exam_schedules')}.*,
				{$this->db->dbprefix('sh_exam_schedules')}.id AS id,
				{$this->db->dbprefix('sh_exam_schedules')}.id AS exam_schedule_subject_id,
				{$this->db->dbprefix('sh_exam_schedules')}.exam_id AS exam_id,
				{$this->db->dbprefix('sh_rooms')}.id AS room_id,
				{$this->db->dbprefix('sh_rooms')}.name AS room_name,
				{$this->db->dbprefix('sh_skills')}.id AS skill_id,
				{$this->db->dbprefix('sh_skills')}.code AS skill_code,
				{$this->db->dbprefix('sh_skills')}.name AS skill_name,
				{$this->db->dbprefix('sh_skills')}.description AS skill_description,
				{$this->db->dbprefix('sh_subjects')}.id AS subject_id,
				{$this->db->dbprefix('sh_subjects')}.name AS subject_name,
				COALESCE({$this->db->dbprefix('exam_std')}.students_male, 0) AS students_male,
				COALESCE({$this->db->dbprefix('exam_std')}.students_female, 0) AS students_female,
				COALESCE({$this->db->dbprefix('exam_std')}.total_students, 0) AS total_students
			");
			$this->db->from('sh_exam_schedules');
			$this->db->join("
					(
						SELECT 
							{$this->db->dbprefix('sh_exam_students')}.exam_id,
							{$this->db->dbprefix('sh_exam_students')}.exam_schedule_id,
							COALESCE(
								SUM(
									IF(
										(LOWER({$this->db->dbprefix('sh_students')}.gender) = 'male' OR LOWER({$this->db->dbprefix('sh_students')}.gender) = 'm'),
										1, 0
									)
								), 0
							) AS students_male,
							COALESCE(
								SUM(
									IF(
										(LOWER({$this->db->dbprefix('sh_students')}.gender) = 'female' OR LOWER({$this->db->dbprefix('sh_students')}.gender) = 'f'),
										1, 0
									)
								), 0
							) AS students_female,
							COUNT(*) AS total_students
						FROM {$this->db->dbprefix('sh_exam_students')}
						LEFT JOIN {$this->db->dbprefix('sh_students')} ON {$this->db->dbprefix('sh_students')}.id = {$this->db->dbprefix('sh_exam_students')}.student_id
						WHERE {$this->db->dbprefix('sh_exam_students')}.exam_id = '{$id}' AND {$this->db->dbprefix('sh_exam_students')}.status = 'active'
						GROUP BY {$this->db->dbprefix('sh_exam_students')}.exam_id, {$this->db->dbprefix('sh_exam_students')}.exam_schedule_id 
					) bpas_exam_std
				", "bpas_exam_std.exam_id = sh_exam_schedules.exam_id AND bpas_exam_std.exam_schedule_id = sh_exam_schedules.row_id", "left");
			$this->db->join('sh_rooms', 'sh_rooms.id = sh_exam_schedules.room_id', 'left');
			$this->db->join('sh_skills', 'sh_skills.id = sh_exam_schedules.skill_id', 'left');
			$this->db->join('sh_subjects', 'sh_subjects.id = sh_exam_schedules.subject_id', 'left');
			$this->db->where('sh_exam_schedules.exam_id', $id);
			if ($row_id) {
				$this->db->where('sh_exam_schedules.row_id', $row_id);
			}
			$this->db->order_by('sh_exam_schedules.id', 'ASC');
    		$q = $this->db->get();
    		if ($q->num_rows() > 0) {
    			foreach (($q->result()) as $row) {
    				$data[$row->row_id][] = $row;
    			}
    			return $data;
    		}
    		return false;
    	}
    	return false;
    }

	public function getExamStudentsByExamID($id, $row_id = null, $status = null) 
    {
    	if (!empty($id) && $id != "") {
			$this->db->select("
				{$this->db->dbprefix('sh_students')}.*,
				{$this->db->dbprefix('sh_study_infos')}.biller_id,
				{$this->db->dbprefix('sh_study_infos')}.academic_year,
				{$this->db->dbprefix('sh_study_infos')}.program_id,
				{$this->db->dbprefix('sh_study_infos')}.skill_id,
				{$this->db->dbprefix('sh_study_infos')}.grade_id,
				{$this->db->dbprefix('sh_study_infos')}.section_id,
				{$this->db->dbprefix('sh_study_infos')}.class_id,
				{$this->db->dbprefix('sh_study_infos')}.timeshift_id,
				{$this->db->dbprefix('sh_study_infos')}.batch,
				{$this->db->dbprefix('sh_exam_students')}.*,
				{$this->db->dbprefix('sh_exam_students')}.id AS exam_student_id,
				{$this->db->dbprefix('sh_exam_students')}.student_id AS student_id,
				{$this->db->dbprefix('sh_exam_students')}.study_info_id AS study_info_id,
				{$this->db->dbprefix('sh_exam_students')}.exam_schedule_id,
				{$this->db->dbprefix('sh_exam_students')}.status as permission_status
			");
			$this->db->from('sh_exam_students');
			$this->db->join('sh_students', 'sh_students.id = sh_exam_students.student_id', 'inner');
			$this->db->join('sh_study_infos', 'sh_study_infos.id = sh_exam_students.study_info_id', 'inner');
			$this->db->where('sh_exam_students.exam_id', $id);
			$this->db->where('sh_exam_students.exam_schedule_id', $row_id);
			if ($status) {
				$this->db->where('sh_exam_students.status', $status);
			}
			$this->db->order_by('sh_students.lastname', 'ASC');
			$this->db->group_by('sh_students.id');
    		$q = $this->db->get();
    		if ($q->num_rows() > 0) {
    			foreach (($q->result()) as $row) {
    				$condition = array(
    					'biller_id'     => $row->biller_id,
    					'academic_year' => $row->academic_year,
    					'program_id'    => $row->program_id,   
    					'skill_id' 		=> $row->skill_id,   
    					'grade_id' 		=> $row->grade_id,   
    					'section_id' 	=> $row->section_id,   
    					'class_id' 		=> $row->class_id,   
    					'timeshift_id'  => $row->timeshift_id,   
    					'batch' 		=> $row->batch 
    				);
    				if ($student = $this->getStudentsByArray($condition, null, $row->student_id)) {
    					$student->status 		  = $row->permission_status;
    					$student->exam_student_id = $row->exam_student_id;
    					$data[] = $student;
    				}
    			}
    			return $data;
    		}
    		return false;
    	}
    	return false;
    }

    public function getStudentsByArray($condition, $term = null, $student_id = null)
    {
    	if (!empty($condition)) {
    		if (isset($condition['batch']) && $condition['batch'] == null) {
    			unset($condition['batch']);
    		}
    		$biller_id     = (isset($condition['biller_id']) ? $condition['biller_id'] : null);
    		$academic_year = (isset($condition['academic_year']) ? $condition['academic_year'] : null);
    		$program_id    = (isset($condition['program_id']) ? $condition['program_id'] : null);
    		$skill_id      = (isset($condition['skill_id']) ? $condition['skill_id'] : null);
    		$grade_id      = (isset($condition['grade_id']) ? $condition['grade_id'] : null);
    		$section_id    = (isset($condition['section_id']) ? $condition['section_id'] : null);
    		$class_id      = (isset($condition['class_id']) ? $condition['class_id'] : null);
    		$timeshift_id  = (isset($condition['timeshift_id']) ? $condition['timeshift_id'] : null);
    		$batch 		   = (isset($condition['batch']) ? $condition['batch'] : null);

    		$stdInfo_where = "";
    		$att_where     = "";
    		if ($biller_id) {
    			$stdInfo_where .= " {$this->db->dbprefix('sh_study_infos')}.biller_id = '{$biller_id}' ";
    			$att_where     .= " {$this->db->dbprefix('sh_attendances')}.biller_id = '{$biller_id}' ";
    		}
    		if ($academic_year) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.academic_year = '{$academic_year}' ";
    			$att_where     .= " AND {$this->db->dbprefix('sh_attendances')}.academic_year = '{$academic_year}' ";
    		}
    		if ($program_id) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.program_id = '{$program_id}' ";
    			$att_where     .= " AND {$this->db->dbprefix('sh_attendances')}.program_id = '{$program_id}' ";
    		}
    		if ($skill_id) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.skill_id = '{$skill_id}' ";
    		}
    		if ($grade_id) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.grade_id = '{$grade_id}' ";
    			$att_where     .= " AND {$this->db->dbprefix('sh_attendances')}.grade_id = '{$grade_id}' ";
    		}
    		if ($section_id) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.section_id = '{$section_id}' ";
    			$att_where     .= " AND {$this->db->dbprefix('sh_attendances')}.section_id = '{$section_id}' ";
    		}
    		if ($class_id) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.class_id = '{$class_id}' ";
    			$att_where     .= " AND {$this->db->dbprefix('sh_attendances')}.class_id = '{$class_id}' ";
    		}
    		if ($timeshift_id) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.timeshift_id = '{$timeshift_id}' ";
    		}
    		if ($batch) {
    			$stdInfo_where .= " AND {$this->db->dbprefix('sh_study_infos')}.batch = '{$batch}' ";
    		}
    		$this->db->select("
    				{$this->db->dbprefix('sh_students')}.*,
    				{$this->db->dbprefix('sh_study_infos')}.*,
					{$this->db->dbprefix('sh_students')}.id AS id,
    				{$this->db->dbprefix('sh_students')}.id AS student_id,
    				{$this->db->dbprefix('sh_study_infos')}.id AS study_info_id,
    				{$this->db->dbprefix('sh_study_infos')}.biller_id AS biller_id,
    				{$this->db->dbprefix('sh_study_infos')}.academic_year AS academic_year,
    				{$this->db->dbprefix('sh_study_infos')}.program_id AS program_id,
    				{$this->db->dbprefix('sh_skills')}.id AS skill_id,
    				{$this->db->dbprefix('sh_skills')}.code AS skill_code,
    				{$this->db->dbprefix('sh_skills')}.name AS skill_name,
    				COALESCE({$this->db->dbprefix('std_attendances')}.present, 0) AS present,
    				COALESCE({$this->db->dbprefix('std_attendances')}.absent, 0) AS absent,
    				COALESCE({$this->db->dbprefix('std_attendances')}.permission, 0) AS permission,
    				COALESCE((COALESCE({$this->db->dbprefix('std_attendances')}.absent, 0) + FLOOR(COALESCE({$this->db->dbprefix('std_attendances')}.permission, 0) / 2)), 0) AS total_attendance,
    				IF(
    					{$this->db->dbprefix('tuition_fee')}.payment_status IS NOT NULL, 
    					IF({$this->db->dbprefix('tuition_fee')}.payment_status != 'due', {$this->db->dbprefix('tuition_fee')}.payment_status, 'pending'), 
    					'pending'
    				) AS fee_status,
    				'active' as status
    			");
    		$this->db->from("sh_students");
    		$this->db->join("sh_study_infos", "sh_students.id = sh_study_infos.student_id", "inner");
    		$this->db->join('sh_programs', 'sh_programs.id = sh_study_infos.program_id', 'left');
    		$this->db->join('sh_skills', 'sh_skills.id = sh_study_infos.skill_id', 'left');
    		$this->db->join("
    				( 
    					SELECT 
    						{$this->db->dbprefix('sh_attendance_items')}.student_id,
    						COALESCE(SUM({$this->db->dbprefix('sh_attendance_items')}.present), 0) AS present,
    						COALESCE(SUM({$this->db->dbprefix('sh_attendance_items')}.absent), 0) AS absent,
    						COALESCE(SUM({$this->db->dbprefix('sh_attendance_items')}.permission), 0) AS permission
    					FROM {$this->db->dbprefix('sh_attendances')} 
    					INNER JOIN {$this->db->dbprefix('sh_attendance_items')} ON {$this->db->dbprefix('sh_attendance_items')}.attendance_id = {$this->db->dbprefix('sh_attendances')}.id
    					" . ($att_where != "" ? (" WHERE " . $att_where) : "") . "
    					GROUP BY {$this->db->dbprefix('sh_attendance_items')}.student_id
    				) bpas_std_attendances
    			", 
    			"std_attendances.student_id = sh_students.id", "left");
    		$this->db->join("
    				(
    					SELECT 
    						{$this->db->dbprefix('sales')}.study_info_id,
    						COALESCE({$this->db->dbprefix('sales')}.payment_status, 'pending') AS payment_status
    					FROM {$this->db->dbprefix('sales')} 
    					INNER JOIN {$this->db->dbprefix('sale_items')} ON {$this->db->dbprefix('sale_items')}.sale_id = {$this->db->dbprefix('sales')}.id
    					LEFT  JOIN {$this->db->dbprefix('products')} ON {$this->db->dbprefix('products')}.id = {$this->db->dbprefix('sale_items')}.product_id
    					LEFT  JOIN {$this->db->dbprefix('categories')} ON {$this->db->dbprefix('categories')}.id = {$this->db->dbprefix('products')}.category_id
    					INNER JOIN {$this->db->dbprefix('companies')} ON {$this->db->dbprefix('companies')}.id = {$this->db->dbprefix('sales')}.customer_id
    					INNER JOIN {$this->db->dbprefix('sh_students')} ON {$this->db->dbprefix('sh_students')}.id = {$this->db->dbprefix('companies')}.student_id
    					WHERE 
    						{$this->db->dbprefix('sales')}.sale_status != 'pending' AND
    						{$this->db->dbprefix('sales')}.study_info_id IS NOT NULL AND 
    						{$this->db->dbprefix('companies')}.student_id IS NOT NULL AND
    						LOWER({$this->db->dbprefix('categories')}.code) LIKE '%tuition%'
    					GROUP BY {$this->db->dbprefix('sales')}.study_info_id
    				) bpas_tuition_fee
    			",
    			"tuition_fee.study_info_id = sh_study_infos.id", "left");
    		$this->db->where('sh_students.status', 'active');
    		if ($stdInfo_where != "") {
    			$this->db->where($stdInfo_where);
    		}
    		if ($student_id) {
    			$this->db->where('sh_students.id', $student_id);
    			$this->db->limit(1);
    		}
			if ($term) {
				$this->db->where("
					(
						{$this->db->dbprefix('sh_students')}.lastname LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.firstname LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.lastname_other LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.firstname_other LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.number LIKE '%" . $term . "%' OR 
						{$this->db->dbprefix('sh_students')}.code LIKE '%" . $term . "%' OR 
						CONCAT(firstname, ' ', lastname) LIKE '%" . $term . "%' OR
						CONCAT(lastname_other, ' ', firstname_other) LIKE '%" . $term . "%' OR
						CONCAT(firstname_other, ' ', lastname_other) LIKE '%" . $term . "%' OR
						CONCAT(lastname, ' ', firstname, ' ', ' (', number, ')') LIKE '%" . $term . "%'
					) 
				");	
				$this->db->limit($this->Settings->rows_per_page);
			}
			$this->db->group_by('sh_students.id');
    		$q = $this->db->get();
    		if ($q->num_rows() > 0) {
    			if ($student_id) {
    				$data = $q->row();
    			} else {
    				foreach (($q->result()) as $row) {
	    				$data[] = $row;
	    			}	
    			}
    			return $data;
    		}
    		return false;
    	}
    	return false;
    }

    public function assignExamStudents($exam_id, $exam_schedule_id, $data) 
    {
    	if (!empty($data)) {
    		$q = $this->db->get_where('sh_exam_students', ['exam_id' => $exam_id, 'exam_schedule_id' => $exam_schedule_id], 1);
    		if ($q->num_rows() > 0) {
    			$this->db->delete('sh_exam_students', ['exam_id' => $exam_id, 'exam_schedule_id' => $exam_schedule_id]);
    		}
    		$this->db->insert_batch('sh_exam_students', $data);
    		return true;
    	}
    	return false;
    }

    public function assignExamStudentSubjectMarks($row_id, $data) 
    {
    	if (!empty($data) && (!empty($row_id) && $row_id != '')) {
    		$q = $this->db->get_where('sh_exam_student_subject_marks', ['row_id' => $row_id], 1);
	   		if ($q->num_rows() > 0) {
	    		$this->db->delete('sh_exam_student_subject_marks', ['row_id' => $row_id]);
	    	}
    		$this->db->insert_batch('sh_exam_student_subject_marks', $data);
    		return true;
    	}
    	return false;
    }

    public function getExamStudentSubjectMarks($row_id) 
    {
    	if (!empty($row_id) && $row_id != '') {
    		$q = $this->db->get_where('sh_exam_student_subject_marks', ['row_id' => $row_id]);
	   		if ($q->num_rows() > 0) {
	   			foreach (($q->result()) as $row) {
	   				$data[$row->exam_student_id][$row->exam_schedule_id] = $row;
	   			}
	   			return $data;
	    	}
    		return false;
    	}
    	return false;
    }

    public function setCreditScorePercentage($academic_year, $data)
    {
    	if (!empty($academic_year) && !empty($data)) {
    		$q = $this->db->get_where('sh_credit_score_percentage', ['academic_year' => $academic_year], 1);
    		if ($q->num_rows() > 0) {
    			$this->db->update('sh_credit_score_percentage', $data, ['id' => $q->row()->id]);
    		} else {
    			$this->db->insert('sh_credit_score_percentage', $data);
    		}
    		return true;
    	}
    	return false;
    }

    public function getCreditScorePercentage($academic_year)
    {
    	if (!empty($academic_year) && $academic_year != '') {
    		$q = $this->db->get_where('sh_credit_score_percentage', ['academic_year' => $academic_year], 1);	
    		if ($q->num_rows() > 0) {
    			return $q->row();
    		}
    		return false;
    	}
    	return false;
    }

    public function getGradePointAverage()
    {
    	$this->db->order_by('max_score', 'DESC');
    	$q = $this->db->get('sh_grade_point_average');
    	if ($q->num_rows() > 0) {
    		foreach (($q->result()) as $row) {
    			$data[] = $row;
    		}
    		return $data;
    	}
    	return false;
    }

    public function setGradePointAverage($data)
    {	
    	if (!empty($data)) {
    		$this->db->empty_table('sh_grade_point_average');
    		$this->db->insert_batch('sh_grade_point_average', $data);
    		return true;
    	}
    	return false;
    }

    public function getStudentStudyDetails($study_info_id)
    {
    	if (!empty($study_info_id) && $study_info_id != '') {
    		$this->db->select("
    				{$this->db->dbprefix('sh_students')}.*,
    				{$this->db->dbprefix('sh_students')}.id AS student_id,
					{$this->db->dbprefix('sh_study_infos')}.id AS study_info_id,
					{$this->db->dbprefix('sh_study_infos')}.batch AS batch,
					{$this->db->dbprefix('sh_programs')}.id AS program_id,
					{$this->db->dbprefix('sh_programs')}.code AS program_code,
					{$this->db->dbprefix('sh_programs')}.name AS program_name,
					{$this->db->dbprefix('sh_skills')}.id AS skill_id,
					{$this->db->dbprefix('sh_skills')}.code AS skill_code,
					{$this->db->dbprefix('sh_skills')}.name AS skill_name,
					{$this->db->dbprefix('sh_grades')}.id AS grade_id,
					{$this->db->dbprefix('sh_grades')}.code AS grade_code,
					{$this->db->dbprefix('sh_grades')}.name AS grade_name,
					{$this->db->dbprefix('sh_sections')}.id AS section_id,
					{$this->db->dbprefix('sh_sections')}.code AS section_code,
					{$this->db->dbprefix('sh_sections')}.name AS section_name,
					{$this->db->dbprefix('sh_classes')}.id AS class_id,
					{$this->db->dbprefix('sh_classes')}.code AS class_code,
					{$this->db->dbprefix('sh_classes')}.name AS class_name,
					{$this->db->dbprefix('custom_field')}.id AS timeshift_id,
					{$this->db->dbprefix('custom_field')}.name AS timeshift_name,
					{$this->db->dbprefix('sh_scholarships')}.name AS scholarship_name,
					{$this->db->dbprefix('companies')}.id AS biller_id,
					IF ({$this->db->dbprefix('companies')}.company != '-', {$this->db->dbprefix('companies')}.company, {$this->db->dbprefix('companies')}.name) AS biller_name
    			");
    		$this->db->from('sh_study_infos');
    		$this->db->join("sh_students", "sh_students.id = sh_study_infos.student_id", "inner");
	    	$this->db->join("companies", "companies.id = sh_study_infos.biller_id", "left");
	    	$this->db->join("sh_programs", "sh_programs.id = sh_study_infos.program_id", "left");
	    	$this->db->join("sh_skills", "sh_skills.id = sh_study_infos.skill_id", "left");
	    	$this->db->join("sh_grades", "sh_grades.id = sh_study_infos.grade_id", "left");
	    	$this->db->join("sh_sections", "sh_sections.id = sh_study_infos.section_id", "left");
	    	$this->db->join("sh_classes", "sh_classes.id = sh_study_infos.class_id", "left");
	    	$this->db->join("custom_field", "custom_field.id = sh_study_infos.timeshift_id", "left");
	    	$this->db->join("sh_scholarships", "sh_scholarships.id = sh_study_infos.scholarship_id", "left");
	    	$this->db->where("sh_study_infos.id", $study_info_id);
	    	$this->db->limit(1);
	    	$q = $this->db->get();
	    	if ($q->num_rows() > 0) {
	    		return $q->row();
	    	}
	    	return false;
    	}
    	return false;
    }

    public function getGradePointAverageByScore($score)
    {
    	if ((!empty($score) && $score != '') || $score === 0) {
    		$this->db->order_by('min_score', 'DESC');
    		$q = $this->db->get('sh_grade_point_average');
    		if ($q->num_rows() > 0) {
    			$result = $q->result();
    			foreach ($result as $key => $row) {
    				if ($key === 0) {
    					if ($score >= $row->min_score) {
    						$data = $row;
    					}
    				} else if ($key != 0 && $key < (count($result) -1)) {
						if ($score >= $row->min_score && $score < $row->max_score) {
    						$data = $row;
    					}
    				} else {
    					if ($score < $row->max_score) {
    						$data = $row;
    					}
    				}
    			}
    			return $data;
    		}
    		return false;
    	}
    	return false;
    }

    public function getMarkByExam_Subject_x_Student($exam_schedule_id, $exam_student_id) 
    {
    	if ((!empty($exam_schedule_id) && $exam_schedule_id != '') && (!empty($exam_student_id) && $exam_student_id != '')) {
			$q = $this->db->get_where("sh_exam_student_subject_marks", ['exam_schedule_id' => $exam_schedule_id, 'exam_student_id' => $exam_student_id], 1);
			if ($q->num_rows() > 0) {
				return $q->row();
			}
			return false;
		}
		return false;
    }

    public function getSubjectMarkGPA($exam_schedule_id, $exam_student_id, $academic_year, $exam_type = null)
    {
    	if ((!empty($exam_schedule_id) && $exam_schedule_id != '') && (!empty($exam_student_id) && $exam_student_id != '')) {
    		if ($subject_mark = $this->getMarkByExam_Subject_x_Student($exam_schedule_id, $exam_student_id)) {
    			if ($percentage = $this->getCreditScorePercentage($academic_year)) {
    				if ($exam_type == 're-exam') {
						$subject_mark->attendance = round(($subject_mark->attendance * 0) / 100);
	    				$subject_mark->assignment = round(($subject_mark->assignment * 0) / 100);
	    				$subject_mark->midterm    = round(($subject_mark->midterm * 0) / 100);
	    				$subject_mark->final      = round(($subject_mark->final * 100) / 100);

	    				$percentage->attendance   = (floatval(0) . '%');
	    				$percentage->assignment   = (floatval(0) . '%');
	    				$percentage->midterm      = (floatval(0) . '%');
	    				$percentage->final        = (floatval(100) . '%');
    				} else {
    					$subject_mark->attendance = round(($subject_mark->attendance * $percentage->attendance) / 100);
	    				$subject_mark->assignment = round(($subject_mark->assignment * $percentage->assignment) / 100);
	    				$subject_mark->midterm    = round(($subject_mark->midterm * $percentage->midterm) / 100);
	    				$subject_mark->final      = round(($subject_mark->final * $percentage->final) / 100);

	    				$percentage->attendance   = (floatval($percentage->attendance) . '%');
	    				$percentage->assignment   = (floatval($percentage->assignment) . '%');
	    				$percentage->midterm      = (floatval($percentage->midterm) . '%');
	    				$percentage->final        = (floatval($percentage->final) . '%');
    				}
    			}
    			$subject_score       = ($subject_mark->attendance + $subject_mark->assignment + $subject_mark->midterm + $subject_mark->final);
				$grade_point_average = $this->getGradePointAverageByScore($subject_score);
				$subject_mark->subject_percentage  = (isset($percentage) ? $percentage : null);
				$subject_mark->subject_score       = $subject_score;
				$subject_mark->grade_point_average = $grade_point_average;
				return $subject_mark;
    		}
    		return false;
    	}
    	return false;
    }

    public function getExamStudentsSubjectsMarkGPA($exam_id, $row_id, $academic_year) 
    {
    	if ((!empty($exam_id) && $exam_id != '') && (!empty($row_id) && $row_id != '')) {
    		$exam           = $this->getExamByID($exam_id);
    		$exam_students  = $this->getExamStudentsByExamID($exam_id, $row_id, 'active');
    		$exam_schedules = $this->getExamScheduleByExamID($exam_id, $row_id);
    		if (!empty($exam_students) && !empty($exam_schedules)) {
    			foreach ($exam_students as $exam_student) {
    				$total_score     = 0;
    				$total_subject   = ((isset($exam_schedules[$row_id]) && !empty($exam_schedules[$row_id])) ? count($exam_schedules[$row_id]) : 0);
    				$student_details = $this->getStudentStudyDetails($exam_student->study_info_id);
    				foreach ($exam_schedules[$row_id] as $exam_schedule) {
    					$subject['subject_details'] = $exam_schedule;
    					$subject['subject_mark']    = $this->getSubjectMarkGPA($exam_schedule->id, $exam_student->exam_student_id, $academic_year, $exam->type);
    					$total_score               += ((isset($subject['subject_mark']->subject_score) && !empty($subject['subject_mark']->subject_score)) ? $subject['subject_mark']->subject_score : 0);
    					$subjects_mark_gpa[]         = (object) $subject;
    				}
    				$total_score_avg = ($total_subject != 0 ? round(($total_score / $total_subject), 2) : 0);
    				$data[] = (object) array(
    					'student_details'   => $student_details,
    					'subjects_mark_gpa' => (isset($subjects_mark_gpa) ? $subjects_mark_gpa : null),
    					'total_subjects'    => $total_subject,
    					'total_score'       => $total_score,
    					'total_score_avg'   => $total_score_avg,
    					'total_score_gpa'   => $this->getGradePointAverageByScore($total_score_avg)
    				);
    			}
    			usort($data, function($obj1, $obj2) { return $obj1->total_score_avg < $obj2->total_score_avg; });
    			$rank_count = 0; $temp = 0;
    			foreach ($data as $row)  {
    				if ((float) $row->total_score_avg != (float) $temp) {
                        $temp = $row->total_score_avg;
                        $rank_count++;
                    }
                    $row->rank = $rank_count;
                    $students_mark_rank[] = $row;
    			}
    			return $students_mark_rank;
    		}
    		return false;
    	}
    	return false;
    }
    public function addFeeType($data = false)
	{
		if($data && $this->db->insert("sh_feestype",$data)){
			return true;
		}
		return false;
	}
	public function updateFeeType($id = false, $data = false){
		if($id && $this->db->update("sh_feestype",$data,array("id"=>$id))){
			return true;
		}
		return false;
	}
    public function getFeeType(){
		$q = $this->db->get('sh_feestype');
		if($q->num_rows() > 0){
			foreach($q->result() as $row){
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}
	public function getFeeTypeByID($id = false)
	{
		$q = $this->db->get_where("sh_feestype", array("id" => $id));
		if($q->num_rows() > 0){
			return $q->row();
		}
		return false;
	}
	public function getAssignClassTeacherByID($id = false)
	{
		$q = $this->db->get_where('sh_assign_class_teacher', array('id' => $id), 1);
		if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
	}
	public function updateAssignClassTeacher($id = false, $data = array())
	{
		if($id && $data && $this->db->where("id",$id)->update('sh_assign_class_teacher', $data)){
			return true;
		}
		return false;
	}
	public function deleteAssignClassTeacherByID($id = false)
	{
		if($id && $this->db->where("id",$id)->delete('sh_assign_class_teacher')){
			return true;
		}
		return false;
	}
	public function addAssignClasssTeacher($data =array())
	{
		if($data && $this->db->insert('sh_assign_class_teacher',$data)){
			return true;
		}
		return false;
	}
}