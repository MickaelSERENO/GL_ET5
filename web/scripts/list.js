/**
 * Created by Miao1 on 14/01/2018.
 */
var myApp = angular.module('myApp', ['checklist-model', 'ngAnimate','angular-notification-icons', 'ngSanitize', 'ui.bootstrap']);

var categories = {
    project: {
        label: "Project",
        fields: {
            id: {label: "Id"},
            manageremail: {label: "Manage"},
            managername:{label:"Responsable"},
            contactemail: {label: "Contact Client"},
            clientname:{label:"Client"},
            name: {label: "Name"},
            description: {label: "Desciption"},
            startdate: {label: "StartDate"},
            enddate: {label: "EndDate"},
            status: {label: "Status"}
        },
        showFields: [
            "status",
            "name",
            "managername",
            "clientname",
            "startdate",
            "enddate"
        ],
        allSearchFields : [
            "name",
            "managername",
            "clientname",
            "contactemail",
            "description",
            "startdate",
            "enddate"
        ],
        userSearchFields : [
            "name",
            "managername",
            "clientname",
            "startdate",
            "enddate"
        ],
        requestDB: "getProjects",
        detailField: "id",
        detailPage:"/Project/infoProject.php?projectID="
    },
    task: {
        label: "Tâche",
        fields: {
            id: {label: "Id"},
            status:{label:"Status"},
            idproject: {label: "idproject"},
            projectname: {label:"Project Name"},
            name:{label:"Name"},
            description: {label: "description"},
            startdate:{label:"Date de début"},
            enddate: {label: "Date de fin"},
            initcharge: {label: "initcharge"},
            computedcharge: {label: "computedcharge"},
            remaining: {label: "remaining"},
            chargeconsumed: {label: "chargeconsumed"},
            advancement: {label: "Adavancement"},
            collaboratoremail: {label: "collaboratoremail"},
            collaborator:{label:"Collaborator"},
            projectenddate: {label: "projectenddate"},
            projectstatus: {label: "projectstatus"}
        },
        showFields: [
            "status",
            "name",
            "projectname",
            "startdate",
            "advancement",
            "enddate"
        ],
        allSearchFields : [
            "name",
            "projectname",
            "collaborator",
            "startdate",
            "enddate",
            "description"
        ],
        userSearchFields : [
            "name",
            "collaborator",
            "startdate",
            "enddate"
        ],
        requestDB: "getTasks"
    },
    contact: {
        label: "Contact",
        fields: {
            name: {label: "Nom"},
            surname: {label: "Prénom"},
            email:{label:"Email"},
            isActive: {label: "Active"}
        },
        showFields: [
            "name",
            "surname",
            "email"
        ],
        allSearchFields : [
            "name",
            "surname"
        ],
        userSearchFields : [
            "name",
            "surname"
        ],
        requestDB: "getContacts"
    }
};

myApp.controller("listControler", function ($scope, $uibModal, $filter, $window) {
    var self = $scope;

    // get all data refering to the category
    self.dataListAll = new Array();
    self.dataList = new Array();
    self.selectCategory = function (c) {
        self.category = c;
        self.name = categories[self.category].label;
        self.fileds = categories[self.category].fields;
        self.showFields = categories[self.category].showFields;
        self.allSearchFields = categories[self.category].allSearchFields;
        self.userSearchFields = categories[self.category].userSearchFields;
        self.detailField = categories[self.category].detailField;

        self.orderColumn = '';

		var httpCtx = new XMLHttpRequest();
		httpCtx.onreadystatechange = function()
		{
			if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
			{
				$scope.$apply(function()
				{
					self.dataListAll = JSON.parse(httpCtx.responseText);
					console.log(self.dataListAll);
					self.arrangeList();
				});
			}
		}
		httpCtx.open('GET', "/AJAX/list.php?function="+categories[self.category].requestDB, true);
		httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		httpCtx.send(null);
    };

    // Get the datalist refering to filters and research demand
    self.arrangeList = function () {
        self.dataList = $filter('filter')(self.dataListAll,self.searchUser);
        self.dataList = $filter('filter')(self.dataList,self.filterUser);
        self.pagination.nbPages = Math.ceil(self.dataList.length/self.pagination.perPage);
        self.goPage(1);
    };


    // order
    self.order = function (column) {
        if (self.orderColumn == column)
            self.orderColumn = '-' + self.orderColumn;
        else
            self.orderColumn = column;
    };

    // pagination
    self.pagination = {};
    self.pagination.nbPages = 1;
    self.pagination.current = 1;
    self.pagination.perPage = "5";
    self.pagination.perPageOptions = ["3","5","10"]; // doesn't work using int
    self.pagination.nbPagesForSelect = 6;
    self.pagination.pagesForSelect = [1];
    self.goPage = function (number) {
        if(self.pagination.nbPages == 0)
            self.pagination.pagesForSelect = [1];
        if(number > self.pagination.nbPages || number < 1 )
            return;
        self.pagination.current = number;
        self.pagination.pagesForSelect = [];
        for(var i=self.pagination.current-self.pagination.nbPagesForSelect/2; i < self.pagination.current + self.pagination.nbPagesForSelect/2; i++){
            if(i>0 && i<=self.pagination.nbPages)
                self.pagination.pagesForSelect.push(i);
        }

    };

    self.inPage = function (index) {
        return index >= (self.pagination.current-1) * parseInt(self.pagination.perPage) &&
            index < self.pagination.current * parseInt(self.pagination.perPage)
            ;
    };

    self.changePerPage = function () {
        self.pagination.nbPages = Math.ceil(self.dataList.length/parseInt(self.pagination.perPage));
        self.goPage(1);
    };


    // filters
    self.showFilters = false;
    self.goFilter = function () {
        self.arrangeList();
    };
    self.filterUser = function (row) {
        switch (self.category) {
            case "project":
                return self.satisfyFilterProjectStatus(row.status)
                    && self.satisfyFilterProjectFinishedFor(row.enddate)
                    && self.satisfyFilterMyprojects(row.manageremail)
                    && self.satisfyFilterMyprojectsCollaborateur(row.collaborateurs);
                break;
            case "task":
                return self.satisfyFilterTaskStatus(row.status)
                    && self.satisfyFilterTaskProjectFinished(row.enddate,row.projectstatus,row.projectenddate)
                    && self.satisfyFilterMyTasks(row.collaboratoremail);
            case "contact":
                return self.satisfyFilterContactActive(row.isActive)
                    && self.satisfyFilterContactRole(row.role)
                    && self.satisfyFilterRelatedToMyProjects(row.itsProjects);
            default:
                break;
        }
        return true;
    };

    self.user = {};
    //      filter projects: status
    self.projectStatus = [
        'NOT_STARTED', 'STARTED', 'CLOSED_VISIBLE', 'CLOSED_INVISIBLE'
    ];
    self.user.projectStatus = ['NOT_STARTED', 'STARTED', 'CLOSED_VISIBLE', 'CLOSED_INVISIBLE'];
    self.satisfyFilterProjectStatus = function (status) {
        return self.user.projectStatus.includes(status);
    };

    //      filter projects: terminés depuis X mois
    self.projectFinishedFor = [
        1, 3, 6, 12, 24
    ];
    self.user.projectFinishedFor = '';
    var _MS_PER_MONTH = 1000 * 60 * 60 * 24 * 30;
    self.satisfyFilterProjectFinishedFor = function (enddate) {
        if(self.user.projectFinishedFor == '')
            return true;
        var currentDate = new Date();
        var endDate = new Date(enddate);
        var monthDiff =  Math.floor((currentDate - endDate) / _MS_PER_MONTH);
        return monthDiff >= self.user.projectFinishedFor ;
    };

    //      filter projects: mes projets
    self.user.myprojects = false;
    self.satisfyFilterMyprojects = function (manageremail) {
        if(self.user.myprojects)
            return self.loggerInfo.contactemail == manageremail;
        else
            return true;
    };

    //      filter projects: mes projects for collaborateurs
    self.satisfyFilterMyprojectsCollaborateur = function (collaborateurs) {
        if(self.isCollaborator){
            return collaborateurs.includes(self.loggerInfo.contactemail)
        }
        return true;
    };

    //      filter tasks: status
    self.taskStatus = [
        'NOT_STARTED', 'STARTED', 'LATE_STARTED', 'LATE_UNSTARTED'
    ];
    self.user.taskStatus = ['NOT_STARTED', 'STARTED', 'LATE_STARTED', 'LATE_UNSTARTED'];
    self.satisfyFilterTaskStatus = function (status) {
        return self.user.taskStatus.includes(status);
    };

    //      filter tasks: finshed task : dans projet terminé depuis X mois
    self.taskProjectFinishedFor = [
        1, 3, 6, 12, 24
    ];
    self.user.taskProjectFinishedFor = '';
    var _MS_PER_MONTH = 1000 * 60 * 60 * 24 * 30;
    self.satisfyFilterTaskProjectFinished = function (enddate, projectstatus, projectenddate) {
        if(self.user.taskProjectFinishedFor == '')
            return true;

        if(projectstatus != 'CLOSED_VISIBLE' && projectstatus != 'CLOSED_INVISIBLE')
            return false;
        var currentDate = new Date();
        var projectEndDate = new Date(projectenddate);

        if(self.user.taskProjectFinishedFor == '')
            return true;
        var monthDiff =  Math.floor((currentDate - projectEndDate) / _MS_PER_MONTH);
        return monthDiff >= self.user.taskProjectFinishedFor ;

    };

    //      filter tasks: my tasks
    self.user.myTasks = true;
    self.satisfyFilterMyTasks = function (collaboratoremail) {
        if(self.user.myTasks)
            return self.loggerInfo.contactemail == collaboratoremail;
        else
            return true;
    };

    //      filter contact: active
    self.contactActive = [
        'Activé', 'Non Activé'
    ];
    self.user.contactActive = ['Activé', 'Non Activé'];
    self.satisfyFilterContactActive = function (status) {
        if(status == 't')
            status = 'Activé';
        else if(status == 'f')
            status = 'Non Activé';
        return self.user.contactActive.includes(status);
    };

    //      filter contact: role : administrator, collaborator, responsable, client
    self.contactRole = [
        'administrator', 'collaborator', 'manager', 'client'
    ];
    self.user.contactRole = ['administrator','collaborator', 'manager', 'client'];
    self.satisfyFilterContactRole = function (role) {

        return self.user.contactRole.some(function (v) {
            return role.indexOf(v) >= 0;
        });
    };

    //      filter contact:  related to one of my projects
    self.user.relatedToMyProjects = false;
    self.satisfyFilterRelatedToMyProjects = function (itsProjects) {
        if(self.user.relatedToMyProjects || self.isCollaborator)
            return itsProjects.some(function (p) {
                return p.manageremail == self.loggerInfo.contactemail;
            }) || itsProjects.some(function (p) {
                    return p.contactemail == self.loggerInfo.contactemail;
                }) || itsProjects.some(function (p) {
                    return p.collaborateurs.includes(self.loggerInfo.contactemail);
                });
        else
            return true;
    };


    // search
    self.search = true;
    self.showSearchSetting = false;
    self.searchText = "";
    self.goSearch = function () {
        self.arrangeList();
    };
    self.searchUser = function (row) {
        if(self.searchText == undefined || self.searchText == "")
            return true;
        return self.satisfySearch(row);
    };
    self.keyPressSearch = function(keyEvent) {
        switch (keyEvent.which) {
            case 13:
                self.goSearch();
                break;
            case 0:
                self.searchText = "";
                self.goSearch();
                break;
            default:
                break;
        }
    };

    self.satisfySearch = function (row) {
        for(var i=0;i< self.userSearchFields.length; i++){
            if(row[self.userSearchFields[i]].toLowerCase().includes(self.searchText.toLowerCase()))
                return true;
        }
    };



    self.goToDetail = function (id) {
        if(categories[self.category].detailPage)
            $window.location.href = categories[self.category].detailPage + id;
    };


    self.loggerInfo = {};
    self.isCollaborator = false;
    self.isManager = false;
	var httpCtx = new XMLHttpRequest();
	httpCtx.onreadystatechange = function()
	{
		if(httpCtx.readyState == 4 && (httpCtx.status == 200 || httpCtx.status == 0))
		{
			$scope.$apply(function()
			{
				self.loggerInfo = JSON.parse(httpCtx.responseText);
				var role = self.loggerInfo.role;
				if(role.includes("manager") || role.includes("administrator")) //same for the filters
					self.isManager = true;
				if(role.includes("collaborator"))
					self.isCollaborator = true;
				self.selectCategory(self.category);
			});
		}
	}
	httpCtx.open('GET', "/AJAX/list.php?function=getLoggerInfo", true);
	httpCtx.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	httpCtx.send(null);

	self.createProject = function()
	{
		var data = JSON.parse(httpCtx.responseText);
		$scope.opts = 
		{
			backdrop : true,
			backdropClick : true,
			dialogFade : false,
			keyboard : true,
			templateUrl : "modalAddProject.html",
			controller : "addProjectModal",
			controllerAs : "$ctrl",
			size: 'lg',
			windowClass: 'my-modal-popup',
			resolve : {
						collList : function(){return arr=[];}
					  }
		};

		var modalInstance = $uibModal.open($scope.opts);
		modalInstance.result.then(
			function(project) //ok
			{
				self.dataListAll.push(project);
				self.arrangeList();
			},
			function() //cancel
			{
			});
	};
});
