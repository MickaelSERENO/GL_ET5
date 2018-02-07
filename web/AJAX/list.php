<?php

	require_once __DIR__.'/../../PSQL/CommonRqst.php';

	$rsqt = new CommonRqst();

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
