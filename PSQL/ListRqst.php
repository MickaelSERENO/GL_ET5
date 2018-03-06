<?php
require_once __DIR__.'/../PSQL/PSQLDatabase.php';

class ListRqst extends PSQLDatabase
{

    public function getLoggerInfo()
    {
        session_start();
        if(!isset($_SESSION["email"]))
        {
            return "Error: non connexion";
        } else {
            $contactemail = $_SESSION["email"];
            return $this->getUserInfo($contactemail);
        }
    }

    public function getUserInfo($email)
    {
        $script = "SELECT * from enduser WHERE contactemail = '$email';";
        $resultScript = pg_query($this->_conn, $script);
        $result = pg_fetch_object($resultScript);
        $result->role = $this->getRole($email);
        return $result;

    }

    public function getRole($email)
    {
        $role = array();
        if($this->isCollaborator($email))
            array_push($role,"collaborator");
        if($this->isClient($email))
            array_push($role,"client");
        if($this->isManager($email))
            array_push($role,"manager");
        if($this->isAdministrator($email))
            array_push($role,"administrator");
        return $role;
    }

    public function isCollaborator($email)
    {
        $script = "select exists (select * from collaborator where useremail = '$email')";

        $resultScript = pg_query($this->_conn, $script);
        $row          = pg_fetch_row($resultScript);
        return $row[0] == "t";
    }

    public function isManager($email)
    {
        $script = "select exists (select * from projectmanager where useremail = '$email')";

        $resultScript = pg_query($this->_conn, $script);
        $row          = pg_fetch_row($resultScript);
        return $row[0] == "t";
    }

    public function isAdministrator($email)
    {
        $script = "select exists (select * from administrator where useremail = '$email')";

        $resultScript = pg_query($this->_conn, $script);
        $row          = pg_fetch_row($resultScript);
        return $row[0] == "t";
    }

    public function isClient($email)
    {
        $script = "select exists (select * from clientcontact where contactemail = '$email')";

        $resultScript = pg_query($this->_conn, $script);
        $row          = pg_fetch_row($resultScript);
        return $row[0] == "t";
    }

    public function getProjects()
    {

        $script = "SELECT p.id,p.name,p.manageremail, p.contactemail, p.description,p.startdate,p.enddate,p.status,c.name as clientname, ct.name as managername
                    FROM project p, clientcontact cc, client c, projectmanager pm, enduser eu, contact ct
                    WHERE  p.contactemail = cc.contactemail and cc.clientemail = c.email
                    and p.manageremail = pm.useremail and pm.useremail = eu.contactemail and eu.contactemail = ct.email
                    ;";

        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_object($resultScript))
        {
            $row->collaborateurs = $this->getProjectCollaborateurs($row->id);
            array_push($result, $row);
        }
        return $result;
    }

    public function getProjectCollaborateurs($projectId)
    {
        $script = "SELECT DISTINCT t.collaboratoremail
                  FROM abstracttask abt, task t 
                  WHERE abt.idproject = '$projectId' and t.id = abt.id;";

        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_object($resultScript))
        {
            array_push($result, $row->collaboratoremail);
        }
        return $result;
    }

    public function getCollaborator()
    {
        $script = "SELECT * FROM collaborator;";

        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_assoc($resultScript))
        {
            array_push($result, $row);
        }
        return $result;
    }

    public function getManager()
    {
        $script = "SELECT * FROM projectmanager;";

        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_assoc($resultScript))
        {
            array_push($result, $row);
        }
        return $result;
    }

    public function getContacts()
    {
        $script = "SELECT name, surname, email FROM contact;";

        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_object($resultScript))
        {
            $row->isActive = $this->getEnduser($row->email)->isactive;
            $row->itsProjects = $this->getContactProjects($row->email);
            $row->role = $this->getRole($row->email);
            if(sizeof($row->role)>0)
            $row->roleUnique = $this->getRole($row->email)[0];
            array_push($result, $row);
        }
        return $result;
    }

    public function getEnduser($contactEmail)
    {
        $script = "SELECT *
                    FROM enduser
                    WHERE contactemail = '$contactEmail'
                    ;";
        $resultScript = pg_query($this->_conn, $script);
        if($result = pg_fetch_object($resultScript))
        {
            return $result;
        }
        else
            return (object)[
                'isactive' => 'f',
            ];

    }

    public function getContactProjects($contactEmail){
        if($this->isCollaborator($contactEmail)) {
            $script = "SELECT p.id, p.name, p.status, p.manageremail, p.contactemail
                  FROM project p, projectcollaborator pc, enduser eu, contact c
                  WHERE p.id = pc.idproject and pc.collaboratoremail = eu.contactemail and eu.contactemail = c.email 
                  and c.email = '$contactEmail'
                  ;";
            $resultScript = pg_query($this->_conn, $script);
            $result = array();
            while($row = pg_fetch_object($resultScript))
            {
                $row->collaborateurs = $this->getProjectCollaborateurs($row->id); //ses collegues
                array_push($result, $row);
            }
            return $result;
        }

        if($this->isManager($contactEmail)) {
            $script = "SELECT p.id, p.name, p.status, p.manageremail, p.contactemail
                  FROM project p, projectcollaborator pc
                  WHERE p.id = pc.idproject and pc.collaboratoremail = '$contactEmail'
                  ;";
            $resultScript = pg_query($this->_conn, $script);
            $result = array();
            while($row = pg_fetch_object($resultScript))
            {
                $row->collaborateurs = $this->getProjectCollaborateurs($row->id); //ses collegues
                array_push($result, $row);
            }
            return $result;
        }

        if($this->isClient($contactEmail)) {
            $script = "SELECT p.id, p.name, p.status, p.manageremail, p.contactemail
                  FROM project p
                  WHERE p.contactemail = '$contactEmail'
                  ;";
            $resultScript = pg_query($this->_conn, $script);
            $result = array();
            while($row = pg_fetch_object($resultScript))
            {
                $row->collaborateurs = $this->getProjectCollaborateurs($row->id); //ses collegues
                array_push($result, $row);
            }
            return $result;
        }

        return ;

    }

    public function getTasks()
    {
        $script = "SELECT *
                    FROM task t,abstracttask abt
                    WHERE t.id = abt.id
                    ;";

        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_object($resultScript))
        {
            $project = $this->getProject($row->idproject);
            $row->projectname = $project->name;
            $row->projectmanageremail= $project->manageremail;
            $row->projectenddate= $project->enddate;
            $row->projectstatus= $project->status;
            $collaborator = $this->getEnduserContact($row->collaboratoremail);
            if(is_object($collaborator))
                $row->collaborator = $collaborator->fullname;
            array_push($result, $row);
        }
        return $result;
    }

    public function getProject($id)
    {

        $script = "SELECT * 
                    FROM project 
                    WHERE  id = $id;";
        $resultScript = pg_query($this->_conn, $script);
        return pg_fetch_object($resultScript);
    }

    public function getEnduserContact($email)
    {
        $script = "SELECT e.*, c.*, concat(name, ' ', surname) as fullName
                    FROM enduser e, contact c
                    WHERE  e.contactemail = '$email' and e.contactemail = c.email;";
        $resultScript = pg_query($this->_conn, $script);
        return pg_fetch_object($resultScript);
    }

    public function addContact($data)
    {
        $data_decode = json_decode($data);
        $script = "select exists (select * from Contact where email = '$data_decode->email')";
        $resultScript = pg_query($this->_conn, $script);
        $row          = pg_fetch_row($resultScript);
        if($row[0] == "t"){
            return -1;
        };
        $script = "INSERT INTO Contact VALUES ('$data_decode->name', '$data_decode->surname', '$data_decode->email', '$data_decode->telephone');";
        pg_query($this->_conn, $script);
        switch ($data_decode->role){
            case "manager":
                $pwd_hash = password_hash($data_decode->pwd,PASSWORD_BCRYPT);
                $script = "INSERT INTO enduser VALUES ('$data_decode->email','$pwd_hash',TRUE);";
                $resultScript = pg_query($this->_conn, $script);
                $script = "INSERT INTO projectmanager VALUES ('$data_decode->email');";
                $resultScript = pg_query($this->_conn, $script);
                break;
            case "collaborator":
                $pwd_hash = password_hash($data_decode->pwd,PASSWORD_BCRYPT);
                $script = "INSERT INTO enduser VALUES ('$data_decode->email','$pwd_hash',TRUE);";
                $resultScript = pg_query($this->_conn, $script);
                $script = "INSERT INTO collaborator VALUES ('$data_decode->email');";
                $resultScript = pg_query($this->_conn, $script);
                break;
            case "client":
                $script = "INSERT INTO clientcontact VALUES ('$data_decode->email','$data_decode->clientemail');";
                $resultScript = pg_query($this->_conn, $script);
                break;
        }
        if(!$resultScript){
            return "Error lors l'ajout de role pour le contact";
        };
        return 1;
    }

}
?>
