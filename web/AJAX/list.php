<?php

	require_once __DIR__.'/../../PSQL/ListRqst.php';

	$rsqt = new ListRqst();

	if(isset($_GET['function'])){
		switch($_GET['function']) {
			case 'getProjects':
                echo json_encode($rsqt->getProjects());
				break;
			case 'getLoggerInfo':
				echo json_encode($rsqt->getLoggerInfo());
				break;
            case 'getCollaborator':
                echo json_encode($rsqt->getCollaborator());
                break;
            case 'getManager':
                echo json_encode($rsqt->getManager());
				break;
			case 'getContacts':
                echo json_encode($rsqt->getContacts());
                break;
            case 'addContact':
            	if($_GET['data'])
                echo json_encode($rsqt->addContact($_GET['data']));
                break;
            case 'getTasks':
                echo json_encode($rsqt->getTasks());
                break;

			default:
				break;
		}
	}

	if(isset($_POST['function'])){
		switch($_POST['function']) {
			default:
				break;
		}
	}

?>
