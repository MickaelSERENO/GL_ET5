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
        return pg_fetch_assoc($resultScript);
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
        $script = "SELECT t.collaboratoremail
                  FROM abstracttask abt, task t 
                  WHERE abt.idproject = '$projectId' and t.id = abt.id;";

        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_object($resultScript))
        {
            array_push($result, $row->collaboratoremail);
        }
        return array_unique($result);
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
        $script = "SELECT p.id, p.name, p.status, p.manageremail
                  FROM project p, projectcollaborator pc, enduser eu, contact c
                  WHERE p.id = pc.idproject and pc.collaboratoremail = eu.contactemail and eu.contactemail = c.email 
                  and c.email = '$contactEmail'
                  ;";
        $resultScript = pg_query($this->_conn, $script);
        $result = array();
        while($row = pg_fetch_object($resultScript))
        {
            array_push($result, $row);
        }
        return $result;
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
            $row->collaborator = $this->getEnduserContact($row->collaboratoremail)->fullname;
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

}
?>
