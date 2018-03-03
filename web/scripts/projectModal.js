myApp.controller("SelectData", function($scope, $uibModalInstance, $filter, data, showFields, fields, okText, title)
{
	$scope.title                       = title;
    $scope.okText                      = okText;
    $scope.dataList                    = [];
    $scope.dataListAll                 = data;
    $scope.currentColl                 = null;

    $scope.pagination                  = {};
    $scope.pagination.nbPages          = 1;
    $scope.pagination.current          = 0;
    $scope.pagination.perPage          = "5";
    $scope.pagination.perPageOptions   = ["3", "5", "10"];
    $scope.pagination.nbPagesForSelect = 6;
    $scope.pagination.pagesForSelect   = [0];
    $scope.goPage = function(id)
    {
        if($scope.pagination.nbPages == 0)
            $scope.pagination.pagesForSelect = [1];
        if(id > $scope.pagination.nbPages || id < 1)
            return;
        $scope.pagination.current        = id;
        $scope.pagination.pagesForSelect = [];

        for(var i=$scope.pagination.current-$scope.pagination.nbPagesForSelect/2; i < $scope.pagination.current + $scope.pagination.nbPagesForSelect/2; i++){
            if(i>0 && i<=$scope.pagination.nbPages)
                $scope.pagination.pagesForSelect.push(i);
        }
    };
    $scope.changePerPage = function() 
    {
        $scope.pagination.nbPages = Math.ceil($scope.dataList.length/parseInt($scope.pagination.perPage));
        $scope.goPage(1);
    };
    $scope.inPage = function(index) 
    {
        return index >= ($scope.pagination.current-1) * parseInt($scope.pagination.perPage) &&
               index < $scope.pagination.current * parseInt($scope.pagination.perPage);
    };

    $scope.searchText   = "";
    $scope.searchFields = showFields;
    $scope.showFields   = showFields;

    $scope.fields       = fields; 
    $scope.goSearch     = function() 
    {
        $scope.arrangeList();
    };
    $scope.arrangeList = function () 
    {
        $scope.dataList           = $filter('filter')($scope.dataListAll,$scope.searchUser);
        $scope.pagination.nbPages = Math.ceil($scope.dataList.length/$scope.pagination.perPage);
        $scope.goPage(1);
    };

    $scope.searchUser = function (row) 
    {
        if($scope.searchText == undefined || $scope.searchText == "")
            return true;
        return $scope.satisfySearch(row);
    };

    $scope.satisfySearch = function(row) 
    {
        for(var i=0;i< $scope.searchFields.length; i++)
            if(row[$scope.searchFields[i]].toLowerCase().includes($scope.searchText.toLowerCase()))
                return true;
        return false;
    };

    $scope.orderColumn = '';
    $scope.order = function (column) 
    {
        if ($scope.orderColumn == column)
            $scope.orderColumn = '-' + $scope.orderColumn;
        else
            $scope.orderColumn = column;
    };
    $scope.arrangeList();

    $scope.selectColl = function(index)
    {
        $scope.currentColl = $scope.dataListAll[index];
    };

    $scope.ok = function()
    {
        $uibModalInstance.close($scope.currentColl);
    };

    $scope.cancel = function()
    {
        $uibModalInstance.dismiss();
    };

    $scope.keyPressSearch = function(keyEvent) 
    {
        switch (keyEvent.which)
        {
            case 13:
                $scope.goSearch();
                break;
            case 0:
                $scope.searchText = "";
                $scope.goSearch();
                break;
            default:
                break;
        }
    };
});
