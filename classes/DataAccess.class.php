<?php

class DataAccess
{
	private $_con,$_query,$_errors;
	
	public function __construct()
	{
		if(!$this->_con=mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME))
		{
			die("connection Error");
		}
		$this->_errors ="";
	} 

	public function escape($str) {
		return mysqli_real_escape_string($this->_con, $str);
	}

	public function query($query)
	{
		if(isset($query))
		{
			$trimmed_query = trim($query);
			$this->_query=$query;
			$res=mysqli_query($this->_con,$this->_query);
			
			if(preg_match('/^select/i', $trimmed_query))
			{
				if(mysqli_num_rows($res))
				{
					$dataArr=array();
					while($row=mysqli_fetch_assoc($res))
					{
						$dataArr[] = $row;
					}
					
					return $dataArr;
				}
				else
				{
					return false;
				}
			}
			else
			{
				if(mysqli_num_rows($res))
				{
					return true;
				}
				else{
					return false;
				}
			}
		}
	} 
	public function login($data,$table,$condition=1)
	{
		$flag=true;
		if(!$this->_checkTable($table))
		{
			$flag=false;
			$this->_errors.="<p>table $table not exist</p>";
		}
		else if(!is_array($data))
		{
			$flag=false;
			$this->_errors .="<p>first parameter shold be an array</p>";
		}
		else if(!$this->_checkFields($data,$table))
		{
			$flag=false;
		}
		if($flag)
		{
			$c=array();
			foreach($data as $ind=>$val)
			{
				$safe_val = $this->escape($val);
				$c[]=$ind."='".$safe_val."' ";
			}
			$this->_query="select * from $table where $condition and ".$c[0]." and ".$c[1];
			$res=mysqli_query($this->_con,$this->_query);
            
			if(mysqli_num_rows($res))
			{
				return mysqli_fetch_array($res);
			}
			else{
				
				return false;
			}
		}
        else{
            return false;
        }
	}
	public function insertFull($data,$table)
	{
		$flag=true;
		if(!$this->_checkTable($table))
		{
			$flag=false;
			$this->_errors.="<p>table $table does not exist</p>";
		}
		else
		{
			if(is_array($data))
			{
				$arrLength = count($data);
				$numFields = $this->_numFields($table);
				if($arrLength != ($numFields - 1))
				{						
					$flag=false;
					$this->_errors .="<p>$table contains $numFields columns and u provided $arrLength values</p>";
				}
				
			}
			else
			{
				$flag=false;
				$this->_errors .="<p>first parameter in the function should be an array</p>";
			}
		}
		if($flag)
		{
			$this->_query="insert into $table values('',";
			foreach($data as $val)
			{
				$safe_val = $this->escape($val);
				$this->_query.="'$safe_val',";
			}
			$this->_chopQuery();
			$this->_query.=")";
			if(mysqli_query($this->_con,$this->_query))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	public function insert($data,$table)
	{
		$flag=true;
		if(!$this->_checkTable($table))
		{
			$flag=false;
			$this->_errors.="<p>table $table not exist</p>";
		}
		else if(!is_array($data))
		{
			$flag=false;
			$this->_errors .="<p>first parameter shold be an array</p>";
		}
		else if(!$this->_checkFields($data,$table))
		{
			$flag=false;
		}
		if($flag)
		{
			$this->_query="insert into $table (";
			foreach($data as $index=>$val)
			{
				$this->_query.="$index,";
			}
			$this->_chopQuery();
			$this->_query.=") values(";
			foreach($data as $val)
			{
				$safe_val = $this->escape($val);
				$this->_query.="'$safe_val',";
			}
			$this->_chopQuery();
			$this->_query.=")";
			if(mysqli_query($this->_con,$this->_query))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
		
	}
    public function getLastId($field,$table,$condition="1")
    {
        $flag=true;
		if(!$this->_checkTable($table))
		{
			$flag=false;
			$this->_errors.="<p>table $table not exist</p>";
		}
        else{
            
            $this->_query="select $field from  $table where $condition order by $field desc limit 1";
			
            $res=mysqli_query($this->_con,$this->_query);
            if(mysqli_num_rows($res))
            {
                $row=mysqli_fetch_array($res);
                return $row[0];
            }
            else{
                $this->_errors.="<p>no id selected</p>";
                return false;
            }
        }
    }
    public function update($data,$table,$condition)
    {
        $flag=true;
		if(!$this->_checkTable($table))
		{
			$flag=false;
			$this->_errors.="<p>table $table not exist</p>";
		}
		else if(!is_array($data))
		{
			$flag=false;
			$this->_errors .="<p>first parameter should be an array</p>";
		}
		else if(!$this->_checkFields($data,$table))
		{
			$flag=false;
		}
		if($flag)
		{
            $this->_query="update $table set ";
            foreach($data as $ind=>$val)
            {
                $safe_val = $this->escape($val);
                $this->_query.=$ind."='".$safe_val."',";
            }
            $this->_chopQuery();
            $this->_query.=" where $condition";
            if(mysqli_query($this->_con,$this->_query))
			{
				return true;
			}
			else
			{
				return false;
			}
            
        }
        else{
            return false;
        }
    }
    public function delete($table,$condition)
    {
        $flag=true;
		if(!$this->_checkTable($table))
		{
			$flag=false;
			$this->_errors.="<p>table $table not exist</p>";
		}
		
		if($flag)
		{
            $this->_query="delete from $table where $condition";
            if(mysqli_query($this->_con,$this->_query))
			{
				return true;
			}
			else
			{
				return false;
			}
            
        }
        else{
            return false;
        }
    }
	public function getAllData($table)
	{
		$this->_query="select * from $table";
		if($resref = mysqli_query($this->_con,$this->_query))
		{
			if(mysqli_num_rows($resref))
			{
				$dataArr=array();
				while($row=mysqli_fetch_assoc($resref))
				{
					$dataArr[] = $row;
				}
				
				return $dataArr;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	public function getData($fields,$table,$condition = "1")
	{
		if(!$this->_checkTable($table))
		{
			$this->_errors.="$table not Present";
			return false;
		}
		$this->_query = "select ";
		
		
		if(is_array($fields))
		{
			if($this->_checkFieldsIndexed($fields,$table))
			{
				$this->_query.=implode(",",$fields);
				
			}
			else
			{
				return false;
			}
		}
		else if(gettype($fields) == "string")
		{
			if($fields == "*")
			{
				$this->_query.="*";
				
			}
			else if($this->_checkFieldsIndexed(array($fields),$table))
			{
				$this->_query.=$fields;
				
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->_errors.=" Invalid scheme type";
			return false;
		}
		$this->_query.=" from $table where $condition";
	
		$resref=mysqli_query($this->_con,$this->_query);
		$dataArr = array();
		if(mysqli_num_rows($resref))
		{		
			while($rows = mysqli_fetch_assoc($resref))
			{
				$dataArr[] = $rows;
			}
		}
		return $dataArr;
			
		
	}
    public function getDataJoin($fields,$table,$condition = "1",$join=NULL)
	{
		
		$this->_query = "select ";
		
		
		if(is_array($fields))
		{
				$this->_query.=implode(",",$fields);
        }
		else if(gettype($fields) == "string")
		{
			if($fields == "*")
			{
				$this->_query.="*";
				
			}
			else 
			{
				$this->_query.=$fields;
				
			}
			
		}
		else
		{
			$this->_errors.=" Invalid scheme type";
			return false;
		}
		$this->_query.=" from $table ";
        if(isset($join) && $join!=NULL)
        {
            if(is_array($join))
            {
                foreach($join as $i=>$v)
                {
                    if(is_array($v))
                    {
                        if($v['1']=='innerjoin' || $v['1']=='join' || $v['1']=='')
                        {
                            $this->_query.=" inner join ".$i." on ".$v['0'];
                        }
                        else if($v['1']=='leftjoin')
                        {
                            $this->_query.=" left join ".$i." on ".$v['0'];
                        }
                        else if($v['1']=='rightjoin')
                        {
                            $this->_query.=" right join ".$i." on ".$v['0'];
                        }
                        
                    }
                }
                
            }
        }
        $this->_query.=" where $condition";
	
		$resref=mysqli_query($this->_con,$this->_query);
		$dataArr = array();
		if(mysqli_num_rows($resref))
		{		
			while($rows = mysqli_fetch_assoc($resref))
			{
				$dataArr[] = $rows;
			}
		}
		return $dataArr;
			
		
	}
    public function selectAsTable($fields,$table,$condition="1",$join=NULL,$actions=null,$config=null)
    {
        $resultArr=$this->getDataJoin($fields,$table,$condition,$join);
        $str="";
        $srno=1;
        foreach($resultArr as $values)
        {
            $str.="<tr>";
            
            if(isset($config['srno']) && $config['srno']==true)
            {
                $str.="<td>";
                $str.=$srno++;
                $str.="</td>";
                
            }
            
            
            foreach($values as $key=>$val)
            {
                $flag=1;
               // validate image array
                if($flag==1)
                {
                    if(isset($config['images']) && isset($config['images'][0]) && is_array($config['images'][0]))
                    {
                        foreach($config['images'] as $i=>$v)
                        {
                            if(isset($v) && $key==$v['field'])
                            {   
                                if(isset($config['hiddenfields']))
                                {   
                                    if(!in_array($key,$config['hiddenfields']))
                                    {
                                        $str.="<td><img src='".$v['path'].$val."' ";
                                        foreach($v['attributes'] as $attr=>$attrval)
                                        {
                                            $str.=$attr."='".$attrval."' ";
                                        }

                                        $str.=" /></td>";
                                        $flag=0;

                                    }

                                }
                                else{
                                    $str.="<td><img src='".$v['path'].$val."' ";
                                        foreach($v['attributes'] as $attr=>$attrval)
                                        {
                                            $str.=$attr."='".$attrval."' ";
                                        }

                                        $str.=" /></td>";
                                        $flag=0;
                                }

                            }
                        }
                        

                    }
                }
                // validate image 
                if($flag==1)
                {
                    if(isset($config['images']) && isset($config['images']['field']) && $key==$config['images']['field'])
                    {

                        if(isset($config['hiddenfields']))
                        {   
                            if(!in_array($key,$config['hiddenfields']))
                            {
                                $str.="<td><img src='".$config['images']['path'].$val."' ";
                                foreach($config['images']['attributes'] as $attr=>$attrval)
                                {
                                    $str.=$attr."='".$attrval."' ";
                                }

                                $str.=" /></td>";
                                $flag=0;

                            }

                        }
                        else{
                            $str.="<td><img src='".$config['images']['path'].$val."' ";
                                foreach($config['images']['attributes'] as $attr=>$attrval)
                                {
                                    $str.=$attr."='".$attrval."' ";
                                }

                                $str.=" /></td>";
                                $flag=0;
                        }

                    }
                }
                // Date time Array
                if($flag==1)
                {
                    if(isset($config['datetime']) && isset($config['datetime'][0]) && is_array($config['datetime'][0]))
                    {
                        foreach($config['datetime'] as $i=>$v)
                        {
                            if(isset($v) && $key==$v['field'])
                            {   
                                if(isset($config['hiddenfields']))
                                {   
                                    if(!in_array($key,$config['hiddenfields']))
                                    {
                                        $str.="<td>".date($v['format'],$val)."</td>";
                                        $flag=0;

                                    }

                                }
                                else{
                                    $str.="<td>".date($v['format'],$val)."</td>";
                                        $flag=0;
                                }

                            }
                        }
                        

                    }
                    
                    
                }
                // Date time
                if($flag==1)
                {
                    if(isset($config['datetime']) && isset($config['datetime']['field']) && $key==$config['datetime']['field'] )
                    {   
                        if(isset($config['hiddenfields']))
                        {   
                            if(!in_array($key,$config['hiddenfields']))
                            {
                                $str.="<td>".date($config['datetime']['format'],$val)."</td>";
                                $flag=0;

                            }

                        }
                        else{
                            $str.="<td>".date($config['datetime']['format'],$val)."</td>";
                                $flag=0;
                        }
                    }
                    
                    
                }
                // theeese isss
                if($flag==1)
                {
                if(isset($config['hiddenfields']))
                {   
                    if(!in_array($key,$config['hiddenfields']))
                    {
                        $str.="<td>".$val."</td>";
                        
                        
                    }
                    
                }
                else
                    $str.="<td>".$val."</td>";
                
                }
               
                
            }
            
            if($actions!=null && is_array($actions))
            {
                
                
                if((isset($config['actions_td']) && $config['actions_td']==false) || !isset($config['actions_td']))
                    $str.="<td>";
                
                foreach($actions as $ind=>$act)
                {
                    if(isset($config['actions_td']) && $config['actions_td']==true)
                       $str.="<td>";
                    
                        $str.="<a href='".$act['link']."?";
                        foreach($act['params'] as $param=>$pvalue)
                        {
                            $str.=$param."=".$values[$pvalue]."&";
                        }
                        $str=$this->_chop_string($str);
                        $str.="' ";

                        foreach($act['attributes'] as $attr=>$attrval)
                        {
                            $str.=$attr."='".$attrval."' ";
                        }
                        $str.=" >".$act['label']."</a>&nbsp;&nbsp;&nbsp;";
                    
                    if(isset($config['actions_td']) && $config['actions_td']==true)
                       $str.="</td>";   
                }
                if((isset($config['actions_td']) && $config['actions_td']==false) || !isset($config['actions_td']))
                    $str.="</td>";
            }
            $str.="</tr>";
        }
        return $str;
    }
	public function count($field,$table,$condition="1")
	{
		if($this->_checkTable($table))
		{
			if($this->_checkFieldsIndexed(array($field),$table))
			{
				$this->_query="select count($field) as count from $table where $condition";
				$resref= mysqli_query($this->_con,$this->_query);
				$row = mysqli_fetch_assoc($resref);
				return $row["count"];
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->_errors.="<p>$table not present</p>";
			return false;
		}
	}
	public function checkExist($field,$value,$table,$condition="1")
	{
		if($this->_checkTable($table))
		{
			if($this->_checkFieldsIndexed(array($field),$table))
			{
				$this->_query ="select * from $table where $field='$value' and $condition";
				$resref = mysqli_query($this->_con,$this->_query);
				if(mysqli_num_rows($resref))
				{
					return true;
				}
				else
				{
					return false;
				}
			} 
			else
			{
				return 0;
			}
		}
		else
		{
			$this->_errors .= "$table not present";
			return 0;
		}
	}
	
	public function createOptions($labelField,$valField,$table,$condition = "1")
	{
		if($this->_checkTable($table))
		{
			if($this->_checkFieldsIndexed(array($labelField,$valField),$table))
			{
				$this->_query = "select $labelField,$valField from $table where $condition";
				
				$resref=mysqli_query($this->_con,$this->_query);
				$data = array();
				
				if(mysqli_num_rows($resref))
				{
					while($row=mysqli_fetch_assoc($resref))
					{
						$data[$row[$labelField]]=$row[$valField];
					}
					return $data;
				}
				else
				{
					return $data;
				}
				
			}
			else
			{
				return array();
			}
		}
		else
		{
			$this->_errors .= "$table not present";
			return 0;
		}
	}
	public function ajaxCreateOptions($labelField,$valField,$table,$condition = "1",$foreign=NULL)
	{
		if($this->_checkTable($table))
		{
			if($foreign!=NULL)
			{
				if($this->_checkFieldsIndexed(array($labelField,$valField,$foreign),$table))
				{
					$this->_query = "select $labelField,$valField,$foreign from $table where $condition";
					
					$resref=mysqli_query($this->_con,$this->_query);
					$data = array();
					
					if(mysqli_num_rows($resref))
					{
						while($row=mysqli_fetch_assoc($resref))
						{
							$data[$row[$labelField]]=array($row[$valField],$row[$foreign]." cn");
						}
						return $data;
					}
					else
					{
						return $data;
					}
					
				}
				else
				{
					return array();
				}
			}
			else
			{
				if($this->_checkFieldsIndexed(array($labelField,$valField),$table))
				{
					$this->_query = "select $labelField,$valField from $table where $condition";
					
					$resref=mysqli_query($this->_con,$this->_query);
					$data = array();
					
					if(mysqli_num_rows($resref))
					{
						while($row=mysqli_fetch_assoc($resref))
						{
							$data[$row[$labelField]]=array($row[$valField],0);
						}
						return $data;
					}
					else
					{
						return $data;
					}
					
				}
				else
				{
					return array();
				}
			}
		}
		else
		{
			$this->_errors .= "$table not present";
			return 0;
		}
	}
    public function last_query()
    {
        return $this->_query;
    }
	public function getErrors()
	{
		return $this->_errors;
	}
	private function _checkTable($table)
	{
		$q="show tables";
		$resref = mysqli_query($this->_con,$q);
		if(mysqli_num_rows($resref))
		{
			while($row=mysqli_fetch_array($resref))
			{
				if($row[0]==$table)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	private function _numFields($table)
	{
		
		if($this->_checkTable($table))
		{
			$q="describe $table";
			$resref = mysqli_query($this->_con,$q);
			return mysqli_num_rows($resref);
		}
		return 0;
		
	}
	private function _checkFields($data,$table)
	{
		if($this->_checkTable($table))
		{
			$fieldArr=array();
			$q="describe $table";
			$resref = mysqli_query($this->_con,$q);
			while($row=mysqli_fetch_assoc($resref))
			{
				$fieldArr[] = $row["Field"];
			}
			$flag = true;
			foreach($data as $index=>$val)
			{
				if(!in_array($index,$fieldArr))
				{
					$flag = false;
					$this->_errors .= "<p>$index not present in $table</p>";
				}
			}
			return $flag;
			
		}
		return 0;
	}
	private function _checkFieldsIndexed($data,$table)
	{
		if($this->_checkTable($table))
		{
			$fieldArr=array();
			$q="describe $table";
			$resref = mysqli_query($this->_con,$q);
			
			while($row=mysqli_fetch_assoc($resref))
			{
				$fieldArr[] = $row["Field"];
			}
			$flag = true;
			foreach($data as $val)
			{
				if(!in_array($val,$fieldArr))
				{
					$flag = false;
					$this->_errors .= "<p>$val not present in $table</p>";
				}
			}
			return $flag;
			
		}
		return 0;
	}
	private function _chopQuery()
	{
		$this->_query=substr($this->_query,0,strlen($this->_query)-1);
	}

	public function getStaffCapacityData($exclude_booking_id = null)
	{
		// 1. Get total active staff by role
		$total_staff = array('chef' => 0, 'server' => 0, 'site_manager' => 0);
		$staff_data = $this->query("SELECT role, COUNT(*) as cnt FROM users WHERE availability_status=1 AND active_status=1 AND role IN ('chef', 'server', 'site_manager') GROUP BY role");
		if (is_array($staff_data)) {
			foreach ($staff_data as $row) {
				$total_staff[$row['role']] = intval($row['cnt']);
			}
		}

		// 2. Get approved leaves in the next 1 year
		$leaves_by_date = array();
		$leaves_data = $this->query("SELECT lr.leave_date, u.role, COUNT(*) as cnt FROM leave_requests lr JOIN users u ON lr.staff_id = u.id WHERE lr.status = 'approved' AND lr.leave_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 YEAR) GROUP BY lr.leave_date, u.role");
		if (is_array($leaves_data)) {
			foreach ($leaves_data as $row) {
				$date = $row['leave_date'];
				$role = $row['role'];
				$leaves_by_date[$date][$role] = intval($row['cnt']);
			}
		}

		// 3. Get assigned staff for bookings in the next 1 year
		$assigned_by_date = array();
		$exclude_cond = "";
		if ($exclude_booking_id !== null) {
			$exclude_cond = " AND b.id != " . intval($exclude_booking_id);
		}
		$assigned_data = $this->query("SELECT b.event_date, bs.assigned_role as role, COUNT(*) as cnt FROM booking_staff bs JOIN bookings b ON bs.booking_id = b.id WHERE b.booking_status IN ('approved', 'completed') $exclude_cond AND b.event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 YEAR) GROUP BY b.event_date, bs.assigned_role");
		if (is_array($assigned_data)) {
			foreach ($assigned_data as $row) {
				$date = $row['event_date'];
				$role = $row['role'];
				$assigned_by_date[$date][$role] = intval($row['cnt']);
			}
		}

		// 4. Determine dates with restricted capacity or fully blocked dates
		$all_dates = array_unique(array_merge(array_keys($leaves_by_date), array_keys($assigned_by_date)));
		
		$date_capacities = array();
		$fully_blocked_dates = array();

		$date_capacities = array();
		$fully_blocked_dates = array();

		// Calculate default maximum capacity
		$default_chef_capacity = $total_staff['chef'] * 350;
		$default_server_capacity = $total_staff['server'] * 250;
		$default_sm_capacity = $total_staff['site_manager'] >= 2 ? 999999 : ($total_staff['site_manager'] >= 1 ? 999 : 0);
		
		$default_capacity = min($default_chef_capacity, $default_server_capacity, $default_sm_capacity);
		$default_capacity = min($default_capacity, 4000);

		foreach ($all_dates as $date) {
			$free_chefs = $total_staff['chef'] - ($leaves_by_date[$date]['chef'] ?? 0) - ($assigned_by_date[$date]['chef'] ?? 0);
			$free_servers = $total_staff['server'] - ($leaves_by_date[$date]['server'] ?? 0) - ($assigned_by_date[$date]['server'] ?? 0);
			$free_sm = $total_staff['site_manager'] - ($leaves_by_date[$date]['site_manager'] ?? 0) - ($assigned_by_date[$date]['site_manager'] ?? 0);

			if ($free_chefs < 1 || $free_servers < 1 || $free_sm < 1) {
				$fully_blocked_dates[] = $date;
				$date_capacities[$date] = 0;
			} else {
				$chef_cap = $free_chefs * 350;
				$server_cap = $free_servers * 250;
				$sm_cap = $free_sm >= 2 ? 999999 : ($free_sm >= 1 ? 999 : 0);
				
				$capacity = min($chef_cap, $server_cap, $sm_cap);
				$capacity = min($capacity, 4000);
				if ($capacity < 10) { // minimum booking size is 10
					$fully_blocked_dates[] = $date;
					$date_capacities[$date] = 0;
				} else {
					$date_capacities[$date] = $capacity;
				}
			}
		}

		return array(
			'default_capacity' => $default_capacity,
			'date_capacities' => $date_capacities,
			'fully_blocked_dates' => $fully_blocked_dates,
			'total_staff' => $total_staff,
			'leaves_by_date' => $leaves_by_date,
			'assigned_by_date' => $assigned_by_date
		);
	}

    private function _chop_string($str)
    {
        return substr($str,0,strlen($str)-1);
    }
}

 require(BASE_PATH.'classes/FileUpload.class.php'); 
?>
