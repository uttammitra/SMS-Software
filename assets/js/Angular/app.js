if (jQuery) {
    var originalFn = $.fn.data;
    $.fn.data = function() {
        if (arguments[0] !== '$binding')
        return originalFn.apply(this, arguments);
    }
}
var schoex = angular.module('schoex',['ngRoute','ngCookies','ngUpload','ui.autocomplete','angularUtils.directives.dirPagination','timer']).run(function($http,dataFactory,$rootScope,$q) {

    $rootScope.defaultAcademicYear = function() {
        angular.forEach($rootScope.dashboardData.academicYear, function (item) {
            if(item.isDefault == "1"){
                return item.id;
            }
        });
    }

});

schoex.config(function($logProvider){
    $logProvider.debugEnabled(false);
});

var appBaseUrl = $('base').attr('href');

schoex.controller('mainController', function(dataFactory,$rootScope,$route,$scope) {
    $scope.chgAcYearModal = function(){
        $scope.modalTitle = $scope.phrase.chgYear;
        $scope.chgAcYearModalShow = !$scope.chgAcYearModalShow;
    }

    $scope.chgAcYear = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/dashboard/changeAcYear','POST',{},{year:$scope.dashboardData.selectedAcYear}).then(function(data) {
            $scope.chgAcYearModalShow = !$scope.chgAcYearModalShow;
            showHideLoad(true);
            location.reload();
        });
    }

    $scope.savePollVote = function(){
        showHideLoad();
        if($scope.dashboardData.polls.selected === undefined){
            alert($scope.phrase.voteMustSelect);
            showHideLoad(true);
            return;
        }
        dataFactory.httpRequest('index.php/dashboard/polls','POST',{},$scope.dashboardData.polls).then(function(data) {
            data = successOrError(data);
            if(data){
                $scope.dashboardData.polls = data;
            }
            showHideLoad(true);
        });
    }

    $scope.adminHasPerm = function(perm){
        if($rootScope.dashboardData.adminPerm == "full"){
            return true;
        }else{
            return $.inArray(perm, $rootScope.dashboardData.adminPerm) > -1;
        }
    }

    $scope.changeTheme = function(theme){
        $('#theme').attr({href: 'assets/css/colors/'+theme+'.css'})
        $rootScope.dashboardData.baseUser.defTheme = theme;

        var updatePost = {'spec':'defTheme','value':theme};
        dataFactory.httpRequest('index.php/accountSettings/profile','POST',{},updatePost).then(function(data) {
            response = apiResponse(data,'edit');
        });

        $('#themecolors').on('click', 'a', function(){
            $('#themecolors li a').removeClass('working');
            $(this).addClass('working')
        });
    }

    $scope.changeLang = function(theme){
        var updatePost = {'spec':'defLang','value':theme};
        dataFactory.httpRequest('index.php/accountSettings/profile','POST',{},updatePost).then(function(data) {
            response = apiResponse(data,'edit');
        });
        location.reload();
    }

    showHideLoad(true);
});

schoex.controller('dashboardController', function(dataFactory,$rootScope,$scope) {
    showHideLoad(true);
});

schoex.controller('upgradeController', function(dataFactory,$rootScope,$scope) {
    showHideLoad(true);
});

schoex.controller('calenderController', function(dataFactory,$scope) {
    showHideLoad(true);
});

schoex.controller('registeration', function(dataFactory,$rootScope,$scope) {
    $scope.views = {};
    $scope.classes = {};
    $scope.views.register = true;
    $scope.form = {};
    $scope.form.studentInfo = [];
    $scope.form.role = "teacher" ;


    dataFactory.httpRequest('index.php/register/classes').then(function(data) {
        $scope.classes = data;
        showHideLoad(true);
    });

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/register/sectionsList','POST',{},{"classes":$scope.form.studentClass}).then(function(data) {
            $scope.sections = data;
        });
    }

    $scope.tryRegister = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/register','POST',{},$scope.form).then(function(data) {
            data = successOrError(data);
            if(data){
                $scope.regId = data.id;
                $scope.changeView("thanks");
            }
            showHideLoad(true);
        });
    }

    $scope.linkStudent = function(){
        $scope.modalTitle = "Link Student";
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert("Min Characters is 3");
            return;
        }
        dataFactory.httpRequest('index.php/register/searchStudents/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(student){
        if(typeof($scope.form.studentInfo) == "undefined"){
            $scope.form.studentInfo = [];
        }
        do{
            var relationShip = prompt("Please enter relationship", "");
        }while(relationShip == "");
        if (relationShip != null && relationShip != "") {
            $scope.form.studentInfo.push({"student":student.name,"relation":relationShip,"id": "" + student.id + "" });
            $scope.showModalLink = !$scope.showModalLink;
        }
    }

    $scope.removeStudent = function(index){
        var confirmRemove = confirm("Sure remove ?");
        if (confirmRemove == true) {
            for (x in $scope.form.studentInfo) {
                if($scope.form.studentInfo[x].id == index){
                    $scope.form.studentInfo.splice(x,1);
                    $scope.form.studentInfoSer = JSON.stringify($scope.form.studentInfo);
                    break;
                }
            }
        }
    }

    $scope.changeView = function(view){
        if(view == "register" || view == "thanks" || view == "show"){
            $scope.form = {};
        }
        $scope.views.register = false;
        $scope.views.thanks = false;
        $scope.views[view] = true;
    }
});

schoex.controller('adminsController', function(dataFactory,$rootScope,$scope) {
    $scope.admins = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.comVia = ["mail","sms","phone"];
    $scope.form.customPermissions = [];

    dataFactory.httpRequest('index.php/admins/listAll').then(function(data) {
        $scope.admins = data;
        showHideLoad(true);
    });

    $scope.saveAdd = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();
            $scope.admins.push(response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/admins/delete/'+item.id,'POST',{},{}).then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.admins.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/admins/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.hasPermission = function(permission){
        var caseNow = $.inArray(permission, $scope.form.customPermissions) > -1;
        return caseNow;
    }

    $scope.saveEdit = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();
            $scope.admins = apiModifyTable($scope.admins,response.id,response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
            $scope.form.comVia = ["mail","sms","phone"];
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('accountantsController', function(dataFactory,$rootScope,$scope) {
    $scope.accountants = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.comVia = ["mail","sms","phone"];

    dataFactory.httpRequest('index.php/accountants/listAll').then(function(data) {
        $scope.accountants = data;
        showHideLoad(true);
    });

    $scope.saveAdd = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();
            $scope.accountants.push(response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/accountants/delete/'+item.id,'POST',{},{}).then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.accountants.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/accountants/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();
            $scope.accountants = apiModifyTable($scope.accountants,response.id,response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
            $scope.form.comVia = ["mail","sms","phone"];
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('classesController', function(dataFactory,$rootScope,$scope) {
    $scope.classes = {};
    $scope.teachers = {};
    $scope.dormitory = {};
    $scope.subject = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.feeTypes = {};
    $scope.form = {};

    dataFactory.httpRequest('index.php/classes/listAll').then(function(data) {
        $scope.classes = data.classes;
        $scope.teachers = data.teachers;
        $scope.dormitory = data.dormitory;
        $scope.subject = data.subject;
        showHideLoad(true);
    });

    $scope.addClass = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeTypes/listAll').then(function(data) {
            $scope.feeTypes = data;
            $scope.changeView('add');
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        $scope.form.allocationValues = $scope.feeTypes;
        dataFactory.httpRequest('index.php/classes','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.classes.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/classes/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.classes.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/classes/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/classes/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.classes = apiModifyTable($scope.classes,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('subjectsController', function(dataFactory,$rootScope,$scope) {
    $scope.subjects = {};
    $scope.teachers = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/subjects/listAll').then(function(data) {
        $scope.subjects = data.subjects;
        angular.forEach($scope.subjects, function(value, key) {
            $scope.subjects[key].teacherId = JSON.parse($scope.subjects[key].teacherId);
        });
        $scope.teachers = data.teachers;
        $scope.classes = data.classes;
        showHideLoad(true);
    });

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/subjects','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                response.teacherId = JSON.parse(response.teacherId);
                $scope.subjects.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/subjects/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.subjects.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/subjects/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/subjects/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                response.teacherId = JSON.parse(response.teacherId);
                $scope.subjects = apiModifyTable($scope.subjects,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('teachersController', function(dataFactory,$rootScope,$scope,$sce) {
    $scope.teachers = {};
    $scope.teachersTemp = {};
    $scope.totalItemsTemp = {};
    $scope.transports = {};
    $scope.teachersApproval = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.comVia = ["mail","sms","phone"];
    $scope.importType ;
    $scope.importReview = {};
    $scope.searchInput = {};

    $scope.import = function(impType){
        $scope.importType = impType;
        $scope.changeView('import');
    };

    $scope.saveImported = function(content){
        content = uploadSuccessOrError(content);
        if(content){
            $scope.importReview = content;
            showHideLoad();
            $scope.changeView('reviewImport');
        }
        showHideLoad(true);
    }

    $scope.reviewImportData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/teachers/reviewImport','POST',{},{'importReview':$scope.importReview}).then(function(data) {
            content = apiResponse(data);
            if(data.status == "failed"){
                $scope.importReview = content;
                $scope.changeView('reviewImport');
            }else{
                getResultsPage('1');
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.removeImport = function(item,index,importType){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            if(importType == "revise"){
                $scope.importReview.revise.splice(index,1);
            }
            if(importType == "ready"){
                $scope.importReview.ready.splice(index,1);
            }
        }
    }

    $scope.showModal = false;
    $scope.teacherProfile = function(id){
        dataFactory.httpRequest('index.php/teachers/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        getResultsPage(newPage);
    };

    $scope.listUsers = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/teachers/listAll/'+pageNumber).then(function(data) {
            $scope.teachers = data.teachers;
            $scope.transports = data.transports;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.searchDB = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/teachers/listAll/'+pageNumber,'POST',{},{'searchInput':$scope.searchInput}).then(function(data) {
            $scope.teachers = data.teachers;
            $scope.transports = data.transports;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.getResultsPage = function(id){
        if ( !jQuery.isEmptyObject($scope.searchInput) ) {
            $scope.searchDB(id);
        }else{
            $scope.listUsers(id);
        }
    }

    $scope.getResultsPage(1);

    $scope.toggleSearch = function(){
        $('.advSearch').toggleClass('col-0 col-3 hidden',1000);
        $('.listContent').toggleClass('col-12 col-9',1000);
    }

    $scope.resetSearch = function(){
        $scope.searchInput = {};
        $scope.getResultsPage(1);
    }

    $scope.sortItems = function(sortBy){
        showHideLoad();
        dataFactory.httpRequest('index.php/teachers/listAll/1','POST',{},{'sortBy':sortBy}).then(function(data) {
            $scope.teachers = data.teachers;
            $scope.totalItems = data.totalItems;
            $rootScope.dashboardData.sort.teachers = sortBy;
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();

            $scope.teachers.push(response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/teachers/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.teachers.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.removeLeaderBoard = function(id,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/teachers/leaderBoard/delete/'+id,'POST').then(function(data) {
                response = apiResponse(data,'edit');
                $scope.teachers[index].isLeaderBoard = "";
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/teachers/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();

            $scope.teachers = apiModifyTable($scope.teachers,response.id,response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.waitingApproval = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/teachers/waitingApproval').then(function(data) {
            $scope.teachersApproval = data;
            $scope.changeView('approval');
            showHideLoad(true);
        });
    }

    $scope.approve = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/teachers/approveOne/'+id,'POST').then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                for (x in $scope.teachersApproval) {
                    if($scope.teachersApproval[x].id == id){
                        $scope.teachersApproval.splice(x,1);
                    }
                }
            }
            $scope.changeView('approval');
            showHideLoad(true);
        });
    }

    $scope.leaderBoard = function(id,index){
        var isLeaderBoard = prompt($rootScope.phrase.leaderBoardMessage);
        if (isLeaderBoard != null) {
            showHideLoad();
            dataFactory.httpRequest('index.php/teachers/leaderBoard/'+id,'POST',{},{'isLeaderBoard':isLeaderBoard}).then(function(data) {
                response = apiResponse(data,'edit');
                $scope.teachers[index].isLeaderBoard = "x";
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
            $scope.form.comVia = ["mail","sms","phone"];
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.approval = false;
        $scope.views.edit = false;
        $scope.views.import = false;
        $scope.views.reviewImport = false;
        $scope.views[view] = true;
    }
});

schoex.controller('studentsController', function(dataFactory,$rootScope,$scope,$sce,$route) {
    $scope.students = {};
    $scope.studentsTemp = {};
    $scope.totalItemsTemp = {};
    $scope.classes = {};
    $scope.sections = {};
    $scope.transports = {};
    $scope.hostel = {};
    $scope.studentsApproval = {};
    $scope.studentMarksheet = {};
    $scope.studentAttendance = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.comVia = ["mail","sms","phone"];
    $scope.userRole ;
    $scope.importType ;
    $scope.importReview;
    $scope.importSections;
    $scope.medViewMode = true;
    $scope.searchInput = {};
    var methodName = $route.current.methodName;

    $scope.listUsers = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/listAll/'+pageNumber).then(function(data) {
            $scope.students = data.students ;
            $scope.classes = data.classes ;
            $scope.sections = data.sections ;
            $scope.transports = data.transports ;
            $scope.hostel = data.hostel ;
            $scope.totalItems = data.totalItems
            $scope.userRole = data.userRole;
            showHideLoad(true);
        });
    }

    $scope.searchDB = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/listAll/'+pageNumber,'POST',{},{'searchInput':$scope.searchInput}).then(function(data) {
            $scope.students = data.students ;
            $scope.classes = data.classes ;
            $scope.sections = data.sections ;
            $scope.transports = data.transports ;
            $scope.hostel = data.hostel ;
            $scope.totalItems = data.totalItems
            $scope.userRole = data.userRole;
            showHideLoad(true);
        });
    }

    $scope.getResultsPage = function(id){
        if ( !jQuery.isEmptyObject($scope.searchInput) ) {
            $scope.searchDB(id);
        }else{
            $scope.listUsers(id);
        }
    }

    $scope.sortItems = function(sortBy){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/listAll/1','POST',{},{'sortBy':sortBy}).then(function(data) {
            $scope.students = data.students ;
            $scope.classes = data.classes ;
            $scope.sections = data.sections ;
            $scope.transports = data.transports ;
            $scope.hostel = data.hostel ;
            $scope.totalItems = data.totalItems
            $scope.userRole = data.userRole;
            $rootScope.dashboardData.sort.students = sortBy;
            showHideLoad(true);
        });
    }

    if(methodName == "marksheet"){
        showHideLoad();
        $scope.isStudent = true;
        dataFactory.httpRequest('index.php/students/marksheet/0').then(function(content) {
            data = apiResponse(content);

            if(content.status == "failed"){
                $scope.noMarksheet = true;
            }else{
                $scope.studentMarksheet = data;
            }

            $scope.changeView('marksheet');
            showHideLoad(true);
        });
    }else{
        $scope.getResultsPage(1);
    }

    $scope.toggleSearch = function(){
        $('.advSearch').toggleClass('col-0 col-3 hidden',1000);
        $('.listContent').toggleClass('col-12 col-9',1000);
    }

    $scope.resetSearch = function(){
        $scope.searchInput = {};
        $scope.getResultsPage(1);
    }

    $scope.import = function(impType){
        $scope.importType = impType;
        $scope.changeView('import');
    };

    $scope.saveImported = function(content){
        content = uploadSuccessOrError(content);
        if(content){
            $scope.importReview = content.dataImport;
            $scope.importSections = content.sections;
            showHideLoad();
            $scope.changeView('reviewImport');
        }
        showHideLoad(true);
    }

    $scope.reviewImportData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/reviewImport','POST',{},{'importReview':$scope.importReview}).then(function(data) {
            content = apiResponse(data);
            if(data.status == "failed"){
                $scope.importReview = content;
                $scope.changeView('reviewImport');
            }else{
                getResultsPage('1');
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.removeImport = function(item,index,importType){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            if(importType == "revise"){
                $scope.importReview.revise.splice(index,1);
            }
            if(importType == "ready"){
                $scope.importReview.ready.splice(index,1);
            }
        }
    }

    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        getResultsPage(newPage);
    };

    $scope.searchSubjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.searchInput.class}).then(function(data) {
            $scope.sections = data.sections;
        });
    }

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.studentClass}).then(function(data) {
            $scope.sections = data.sections;
        });
    }

    $scope.saveAdd = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();
            $scope.students.push(response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/students/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.students.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.removeStAcYear = function(student,acYear,index){
        var confirmRemoveAcYear = confirm($rootScope.phrase.sureRemove);
        if (confirmRemoveAcYear == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/students/acYear/delete/'+student+'/'+acYear,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.form.studentAcademicYears.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();
            $scope.students = apiModifyTable($scope.students,response.id,response);
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.waitingApproval = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/waitingApproval').then(function(data) {
            $scope.studentsApproval = data;
            $scope.changeView('approval');
            showHideLoad(true);
        });
    }

    $scope.gradStdList = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/gradStdList').then(function(data) {
            $scope.gradStdList = data;
            $scope.changeView('grad');
            showHideLoad(true);
        });
    }

    $scope.approve = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/approveOne/'+id,'POST').then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                for (x in $scope.studentsApproval) {
                    if($scope.studentsApproval[x].id == id){
                        $scope.studentsApproval.splice(x,1);
                    }
                }
            }
            $scope.changeView('approval');
            showHideLoad(true);
        });
    }

    $scope.marksheet = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/marksheet/'+id).then(function(data) {
            data = apiResponse(data);
            if(data){
                $scope.studentMarksheet = data;
                $scope.changeView('marksheet');
            }
            showHideLoad(true);
        });
    }

    $scope.leaderBoard = function(id,index){
        var isLeaderBoard = prompt($rootScope.phrase.leaderBoardMessage);
        if (isLeaderBoard != null) {
            showHideLoad();
            dataFactory.httpRequest('index.php/students/leaderBoard/'+id,'POST',{},{'isLeaderBoard':isLeaderBoard}).then(function(data) {
                apiResponse(data,'edit');
                $scope.students[index].isLeaderBoard = "x";
                showHideLoad(true);
            });
        }
    }

    $scope.removeLeaderBoard = function(id,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/students/leaderBoard/delete/'+id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.students[index].isLeaderBoard = "";
                }
                showHideLoad(true);
            });
        }
    }

    $scope.attendance = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/attendance/'+id).then(function(data) {
            $scope.studentAttendance = data;
            $scope.changeView('attendance');
            showHideLoad(true);
        });
    }

    $scope.medicalRead = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/medical/'+id).then(function(data) {
            $scope.medicalInfo = {};
            $scope.medicalInfo.data = data;
            $scope.medicalInfo.userId = id;
            $scope.changeView('medical');
            showHideLoad(true);
        });
    }

    $scope.medicalToggle = function(){
        $scope.medViewMode = !$scope.medViewMode;
    }

    $scope.saveMedical = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/students/medical','POST',{},$scope.medicalInfo).then(function(data) {
            response = apiResponse(data,'edit');
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
            $scope.form.comVia = ["mail","sms","phone"];
        }
        $scope.views.list = false;
        $scope.views.approval = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.attendance = false;
        $scope.views.marksheet = false;
        $scope.views.import = false;
        $scope.views.reviewImport = false;
        $scope.views.medical = false;
        $scope.views.grad = false;
        $scope.views[view] = true;
    }
});

schoex.controller('parentsController', function(dataFactory,$scope,$sce,$rootScope) {
    $scope.stparents = {};
    $scope.stparentsTemp = {};
    $scope.totalItemsTemp = {};
    $scope.stparentsApproval = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.comVia = ["mail","sms","phone"];
    $scope.form.studentInfo = {};
    $scope.importType ;
    $scope.searchResults = {};
    $scope.searchInput = {};
    $scope.userRole = $rootScope.dashboardData.role;

    $scope.import = function(impType){
        $scope.importType = impType;
        $scope.changeView('import');
    };

    $scope.saveImported = function(content){
        content = uploadSuccessOrError(content);
        if(content){
            $scope.importReview = content;
            showHideLoad();
            $scope.changeView('reviewImport');
        }
        showHideLoad(true);
    }

    $scope.reviewImportData = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/parents/reviewImport','POST',{},{'importReview':$scope.importReview}).then(function(data) {
            content = apiResponse(data);
            if(data.status == "failed"){
                $scope.importReview = content;
                $scope.changeView('reviewImport');
            }else{
                getResultsPage('1');
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.removeImport = function(item,index,importType){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            if(importType == "revise"){
                $scope.importReview.revise.splice(index,1);
            }
            if(importType == "ready"){
                $scope.importReview.ready.splice(index,1);
            }
        }
    }

    $scope.showModal = false;
    $scope.parentProfile = function(id){
        dataFactory.httpRequest('index.php/parents/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.listUsers = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/parents/listAll/'+pageNumber).then(function(data) {
            $scope.stparents = data.parents;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.searchDB = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/parents/listAll/'+pageNumber,'POST',{},{'searchInput':$scope.searchInput}).then(function(data) {
            $scope.stparents = data.parents;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.getResultsPage = function(id){
        if ( !jQuery.isEmptyObject($scope.searchInput) ) {
            $scope.searchDB(id);
        }else{
            $scope.listUsers(id);
        }
    }

    $scope.getResultsPage(1);

    $scope.toggleSearch = function(){
        $('.advSearch').toggleClass('col-0 col-3 hidden',1000);
        $('.listContent').toggleClass('col-12 col-9',1000);
    }

    $scope.resetSearch = function(){
        $scope.searchInput = {};
        $scope.getResultsPage(1);
    }

    $scope.sortItems = function(sortBy){
        showHideLoad();
        dataFactory.httpRequest('index.php/parents/listAll/1','POST',{},{'sortBy':sortBy}).then(function(data) {
            $scope.stparents = data.parents;
            $scope.totalItems = data.totalItems;
            $rootScope.dashboardData.sort.teachers = sortBy;
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(data){
        showHideLoad();
        response = apiResponse(data,'add');
        if(data.status == "success"){
            $scope.stparents.push(response);
            $scope.changeView('list');
            showHideLoad(true);
        }
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/parents/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.stparents.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.removeStudent = function(index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            for (x in $scope.form.studentInfo) {
                if($scope.form.studentInfo[x].id == index){
                    $scope.form.studentInfo.splice(x,1);
                    $scope.form.studentInfoSer = JSON.stringify($scope.form.studentInfo);
                    break;
                }
            }
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/parents/'+id).then(function(data) {
            $scope.form = data;
            if(data.parentOf == null || data.parentOf == ''){
                $scope.form.studentInfo = [];
            }else{
                $scope.form.studentInfo = data.parentOf;
            }
            $scope.form.studentInfoSer = JSON.stringify($scope.form.studentInfo);
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.monitorParentChange = function(){
        $scope.form.studentInfoSer = JSON.stringify($scope.form.studentInfo);
    }

    $scope.saveEdit = function(data){
        showHideLoad();
        response = apiResponse(data,'add');
        if(data.status == "success"){
            $scope.stparents = apiModifyTable($scope.stparents,response.id,response);
            $scope.changeView('list');
            showHideLoad(true);
        }
    }

    $scope.waitingApproval = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/parents/waitingApproval').then(function(data) {
            $scope.stparentsApproval = data;
            $scope.changeView('approval');
            showHideLoad(true);
        });
    }

    $scope.approve = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/parents/approveOne/'+id,'POST').then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                for (x in $scope.stparentsApproval) {
                    if($scope.stparentsApproval[x].id == id){
                        $scope.stparentsApproval.splice(x,1);
                    }
                }
            }
            $scope.changeView('approval');
            showHideLoad(true);
        });
    }

    $scope.linkStudent = function(){
        $scope.modalTitle = $rootScope.phrase.linkStudentParent;
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.minCharLength3);
            return;
        }
        dataFactory.httpRequest('index.php/parents/search/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(student){
        do{
            var relationShip = prompt("Please enter relationship", "");
        }while(relationShip == "");
        if (relationShip != null && relationShip != "") {
            $scope.form.studentInfo.push({"student":student.name,"relation":relationShip,"id": "" + student.id + "" });
            $scope.form.studentInfoSer = JSON.stringify($scope.form.studentInfo);
            $scope.showModalLink = !$scope.showModalLink;
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
            $scope.form.comVia = ["mail","sms","phone"];
            $scope.form.studentInfo = [];
        }
        $scope.views.list = false;
        $scope.views.approval = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.import = false;
        $scope.views.reviewImport = false;
        $scope.views[view] = true;
    }
});

schoex.controller('newsboardController', function(dataFactory,$routeParams,$sce,$rootScope,$scope) {
    $scope.newsboard = {};
    $scope.newsboardTemp = {};
    $scope.totalItemsTemp = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;

    if($routeParams.newsId){
        showHideLoad();
        dataFactory.httpRequest('index.php/newsboard/'+$routeParams.newsId).then(function(data) {
            $scope.form = data;
            $scope.newsTitle = data.newsTitle;
            $scope.newsText = $sce.trustAsHtml(data.newsText);
            $scope.changeView('read');
            showHideLoad(true);
        });
    }else{
        $scope.totalItems = 0;
        $scope.pageChanged = function(newPage) {
            getResultsPage(newPage);
        };

        getResultsPage(1);
    }

    function getResultsPage(pageNumber) {
        if(! $.isEmptyObject($scope.newsboardTemp)){
            dataFactory.httpRequest('index.php/newsboard/search/'+$scope.searchText+'/'+pageNumber).then(function(data) {
                angular.forEach(data.newsboard, function (item) {
                    item.newsText = $sce.trustAsHtml(item.newsText);
                });
                $scope.newsboard = data.newsboard;
                $scope.totalItems = data.totalItems;
            });
        }else{
            dataFactory.httpRequest('index.php/newsboard/listAll/'+pageNumber).then(function(data) {
                angular.forEach(data.newsboard, function (item) {
                    item.newsText = $sce.trustAsHtml(item.newsText);
                });
                $scope.newsboard = data.newsboard;
                $scope.userRole = data.userRole;
                $scope.totalItems = data.totalItems;
                showHideLoad(true);
            });
        }
    }

    $scope.searchDB = function(){
        if($scope.searchText.length >= 3){
            if($.isEmptyObject($scope.newsboardTemp)){
                $scope.newsboardTemp = $scope.newsboard ;
                $scope.totalItemsTemp = $scope.totalItems;
                $scope.newsboard = {};
            }
            getResultsPage(1);
        }else{
            if(! $.isEmptyObject($scope.newsboardTemp)){
                $scope.newsboard = $scope.newsboardTemp ;
                $scope.totalItems = $scope.totalItemsTemp;
                $scope.newsboardTemp = {};
            }
        }
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/newsboard','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                response.newsText = $sce.trustAsHtml(response.newsText);
                $scope.newsboard.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/newsboard/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.newsboard.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/newsboard/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/newsboard/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                response.newsText = $sce.trustAsHtml(response.newsText);
                $scope.newsboard = apiModifyTable($scope.newsboard,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('libraryController', function(dataFactory,$rootScope,$scope) {
    $scope.library = {};
    $scope.libraryTemp = {};
    $scope.totalItemsTemp = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        getResultsPage(newPage);
    };

    getResultsPage(1);
    function getResultsPage(pageNumber) {
        if(! $.isEmptyObject($scope.libraryTemp)){
            dataFactory.httpRequest('index.php/library/search/'+$scope.searchText+'/'+pageNumber).then(function(data) {
                $scope.library = data.bookLibrary;
                $scope.totalItems = data.totalItems;
            });
        }else{
            dataFactory.httpRequest('index.php/library/listAll/'+pageNumber).then(function(data) {
                $scope.library = data.bookLibrary;
                $scope.totalItems = data.totalItems;
                $scope.userRole = data.userRole;
                showHideLoad(true);
            });
        }
    }

    $scope.searchDB = function(){
        if($scope.searchText.length >= 3){
            if($.isEmptyObject($scope.libraryTemp)){
                $scope.libraryTemp = $scope.library ;
                $scope.totalItemsTemp = $scope.totalItems;
                $scope.library = {};
            }
            getResultsPage(1);
        }else{
            if(! $.isEmptyObject($scope.libraryTemp)){
                $scope.library = $scope.libraryTemp ;
                $scope.totalItems = $scope.totalItemsTemp;
                $scope.libraryTemp = {};
            }
        }
    }

    $scope.saveAdd = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();

            $scope.library.push(response);
            $scope.changeView('list');
            showHideLoad(true);
        }
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/library/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.library.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/library/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();

            $scope.library = apiModifyTable($scope.library,response.id,response);
            $scope.changeView('list');
            showHideLoad(true);
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});


schoex.controller('accountSettingsController', function(dataFactory,$rootScope,$scope,$route) {
    $scope.account = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.languages = {};
    $scope.languageAllow ;
    var methodName = $route.current.methodName;

    $scope.changeView = function(view){
        if(view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.profile = false;
        $scope.views.email = false;
        $scope.views.password = false;
        $scope.views.invoices = false;
        $scope.views.invoiceDetails = false;
        $scope.views[view] = true;
    }

    if(methodName == "profile"){
        dataFactory.httpRequest('index.php/accountSettings/langs').then(function(data) {
            $scope.languages = data.languages;
            $scope.languageAllow = data.languageAllow;
            $scope.layoutColorUserChange = data.layoutColorUserChange;
            showHideLoad(true);
        });
        dataFactory.httpRequest('index.php/accountSettings/data').then(function(data) {
            $scope.form = data;
            $scope.oldThemeVal = data.defTheme;
            $scope.defLang = data.defLang;
            $scope.changeView('profile');
            showHideLoad(true);
        });
    }else if(methodName == "email"){
        $scope.form = {};
        $scope.changeView('email');
        showHideLoad(true);
    }else if(methodName == "password"){
        $scope.form = {};
        $scope.changeView('password');
        showHideLoad(true);
    }else if(methodName == "invoices"){
        dataFactory.httpRequest('index.php/accountSettings/invoices').then(function(data) {
            $scope.invoices = data.invoices;
            $scope.changeView('invoices');
            showHideLoad(true);
        });
    }

    $scope.seeInvoice = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/accountSettings/invoices/'+id).then(function(data) {
            $scope.invoice = data;
            $scope.changeView('invoiceDetails');
            showHideLoad(true);
        });
    }

    $scope.payOnline = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/invoice/'+id).then(function(data) {
            $scope.invoice = data;
            $scope.modalTitle = "Pay Invoice Online";
            $scope.payOnlineModal = !$scope.payOnlineModal;
            showHideLoad(true);
        });
    }

    $scope.payOnlineNow = function(id){
        $scope.form.invoice = id;
    }

    $scope.saveEmail = function(){
        if($scope.form.email != $scope.form.reemail){
            alert($rootScope.phrase.mailReMailDontMatch);
        }else{
            showHideLoad();
            dataFactory.httpRequest('index.php/accountSettings/email','POST',{},$scope.form).then(function(data) {
                response = apiResponse(data,'edit');
                showHideLoad(true);
            });
        }
    }

    $scope.savePassword = function(){
        if($scope.form.newPassword != $scope.form.repassword){
            alert($rootScope.phrase.passRepassDontMatch);
        }else{
            showHideLoad();
            dataFactory.httpRequest('index.php/accountSettings/password','POST',{},$scope.form).then(function(data) {
                response = apiResponse(data,'edit');
                showHideLoad(true);
            });
        }
    }

    $scope.saveProfile = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/accountSettings/profile','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(response){
                if($scope.form.defTheme != $scope.oldThemeVal){
                    location.reload();
                }
                if($scope.form.defLang != $scope.defLang){
                    location.reload();
                }
                $scope.form = response;
            }
            showHideLoad(true);
        });
    }
});

schoex.controller('classScheduleController', function(dataFactory,$rootScope,$scope,$sce) {
    $scope.classes = {};
    $scope.subject = {};
    $scope.days = {};
    $scope.classSchedule = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;

    dataFactory.httpRequest('index.php/classschedule/listAll').then(function(data) {
        $scope.classes = data.classes;
        $scope.subject = data.subject;
        $scope.teachers = data.teachers;
        $scope.sections = data.sections;
        $scope.userRole = data.userRole;
        $scope.days = data.days;
        showHideLoad(true);
    });

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/classschedule/'+id).then(function(data) {
            $scope.classSchedule = data;
            $scope.classId = id;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.removeSub = function(id,day){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/classschedule/delete/'+$scope.classId+'/'+id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    for (x in $scope.classSchedule[day].sub) {
                        if($scope.classSchedule[day].sub[x].id == id){
                            $scope.classSchedule[day].sub.splice(x,1);
                        }
                    }
                }
                showHideLoad(true);
            });
        }
    }

    $scope.addSubOne = function(day){
        $scope.form = {};
        $scope.form.dayOfWeek = day;

        $scope.modalTitle = $rootScope.phrase.addSch;
        $scope.scheduleModal = !$scope.scheduleModal;
    }

    $scope.saveAddSub = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/classschedule/'+$scope.classId,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                if(! $scope.classSchedule[response.dayOfWeek].sub){
                    $scope.classSchedule[response.dayOfWeek].sub = [];
                }
                $scope.classSchedule[response.dayOfWeek].sub.push({"id":response.id,"classId":response.classId,"subjectId":response.subjectId,"start":response.startTime,"end":response.endTime});
            }
            $scope.scheduleModal = !$scope.scheduleModal;
            showHideLoad(true);
        });
    }

    $scope.editSubOne = function(id,day){
        showHideLoad();
        $scope.form = {};
        dataFactory.httpRequest('index.php/classschedule/sub/'+id).then(function(data) {
            $scope.form = data;
            $scope.oldDay = day;

            $scope.modalTitle = $rootScope.phrase.editSch;
            $scope.scheduleModalEdit = !$scope.scheduleModalEdit;
            showHideLoad(true);
        });
    }

    $scope.saveEditSub = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/classschedule/sub/'+id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                for (x in $scope.classSchedule[$scope.oldDay].sub) {
                    if($scope.classSchedule[$scope.oldDay].sub[x].id == id){
                        $scope.classSchedule[$scope.oldDay].sub.splice(x,1);
                    }
                }
                if(! $scope.classSchedule[response.dayOfWeek].sub){
                    $scope.classSchedule[response.dayOfWeek].sub = [];
                }
                $scope.classSchedule[response.dayOfWeek].sub.push({"id":response.id,"classId":response.classId,"subjectId":response.subjectId,"start":response.startTime,"end":response.endTime});
            }
            $scope.scheduleModalEdit = !$scope.scheduleModalEdit;
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.editSub = false;
        $scope.views.addSub = false;
        $scope.views[view] = true;
    }
});


schoex.controller('settingsController', function(dataFactory,$rootScope,$scope,$route) {
    $scope.views = {};
    $scope.form = {};
    $scope.languages = {};
    $scope.newDayOff ;
    var methodName = $route.current.methodName;
    $scope.oldThemeVal;

    $scope.changeView = function(view){
        $scope.views.settings = false;
        $scope.views.terms = false;
        $scope.views[view] = true;
    }

    if(methodName == "settings"){
        dataFactory.httpRequest('index.php/siteSettings/langs').then(function(data) {
            $scope.languages = data.languages;
            showHideLoad(true);
        });
        dataFactory.httpRequest('index.php/siteSettings/siteSettings').then(function(data) {
            $scope.form = data.settings;
            $scope.timezone_list = data.timezone_list;
            $scope.formS = data.smsProvider;
            $scope.formM = data.mailProvider;
            $scope.oldThemeVal = $scope.form.layoutColor;
            $scope.globalcalendars = data.globalcalendars;
            showHideLoad(true);
        });
        $scope.changeView('settings');
    }else if(methodName == "terms"){
        dataFactory.httpRequest('index.php/siteSettings/terms').then(function(data) {
            $scope.form = data;
            showHideLoad(true);
        });
        $scope.changeView('terms');
    }

    $scope.isDaySelected = function(arrayData,valueData){
        return arrayData.indexOf(valueData) > -1;
    }

    $scope.officialVacationDayAdd = function(){
        if($scope.newDayOff == '' || typeof $scope.newDayOff === "undefined"){ return; }
        $scope.form.officialVacationDay.push($scope.newDayOff);
    }

    $scope.removeVacationDay = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            $scope.form.officialVacationDay.splice(index,1);
        }
    }

    $scope.moduleActivated = function(module){
        return $.inArray(module, $scope.form.activatedModules) > -1;
    }

    $scope.saveEdit = function(){
        showHideLoad();
        $scope.form.smsProvider = $scope.formS;
        $scope.form.mailProvider = $scope.formM;
        dataFactory.httpRequest('index.php/siteSettings/siteSettings','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            location.reload();
            showHideLoad(true);
        });
    }

    $scope.saveTerms = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/siteSettings/terms','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            showHideLoad(true);
        });
    }

});

schoex.controller('attendanceController', function(dataFactory,$scope) {
    $scope.classes = {};
    $scope.attendanceModel;
    $scope.subjects = {};
    $scope.views = {};
    $scope.form = {};
    $scope.userRole ;
    $scope.class = {};
    $scope.subject = {};
    $scope.students = {};

    dataFactory.httpRequest('index.php/attendance/data').then(function(data) {
        $scope.classes = data.classes;
        $scope.subjects = data.subject;
        $scope.attendanceModel = data.attendanceModel;
        $scope.userRole = data.userRole;
        $scope.changeView('list');
        showHideLoad(true);
    });

    $scope.selectAll = function(type){
        if ($scope.selectedAll) {
            $scope.selectedAll = true;
        } else {
            $scope.selectedAll = false;
        }
        angular.forEach($scope.students, function (item) {
            item.attendance = type;
        });
    }

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.subjects = data.subjects;
            $scope.sections = data.sections;
        });
    }

    $scope.startAttendance = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/attendance/list','POST',{},$scope.form).then(function(data) {
            $scope.class = data.class;
            if(data.subject){
                $scope.subject = data.subject;
            }
            $scope.students = data.students;
            $scope.changeView('lists');
            showHideLoad(true);
        });
    }

    $scope.saveAttendance = function(){
        showHideLoad();
        $scope.form.classId = $scope.class.id;
        $scope.form.attendanceDay = $scope.form.attendanceDay;
        $scope.form.stAttendance = $scope.students;
        if($scope.subject.id){
            $scope.form.subject = $scope.subject.id;
        }
        dataFactory.httpRequest('index.php/attendance','POST',{},$scope.form).then(function(data) {
            apiResponse(data,'add');
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        $scope.views.list = false;
        $scope.views.lists = false;
        $scope.views.edit = false;
        $scope.views.editSub = false;
        $scope.views.addSub = false;
        $scope.views[view] = true;
    }
});

schoex.controller('staffAttendanceController', function(dataFactory,$scope) {
    $scope.views = {};
    $scope.form = {};
    $scope.views.list = true;
    $scope.teachers = {};

    showHideLoad(true);
    $scope.startAttendance = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/sattendance/list','POST',{},$scope.form).then(function(data) {
            $scope.teachers = data.teachers;
            $scope.changeView('lists');
            showHideLoad(true);
        });
    }

    $scope.selectAll = function(type){
        if ($scope.selectedAll) {
            $scope.selectedAll = true;
        } else {
            $scope.selectedAll = false;
        }
        angular.forEach($scope.teachers, function (item) {
            item.attendance = type;
        });
    }

    $scope.saveAttendance = function(){
        showHideLoad();
        $scope.form.attendanceDay = $scope.form.attendanceDay;
        $scope.form.stAttendance = $scope.teachers;
        dataFactory.httpRequest('index.php/sattendance','POST',{},$scope.form).then(function(data) {
            apiResponse(data,'add');
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        $scope.views.list = false;
        $scope.views.lists = false;
        $scope.views.edit = false;
        $scope.views.editSub = false;
        $scope.views.addSub = false;
        $scope.views[view] = true;
    }
});

schoex.controller('reportsController', function(dataFactory,$rootScope,$scope,$http,$sce) {
    $scope.views = {};
    $scope.form = {};
    $scope.views.list = true;
    $scope.stats = {};

    showHideLoad(true);
    $scope.usersStats = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'usersStats'}).then(function(data) {
            $scope.stats = data;
            $scope.changeView('usersStats');
            showHideLoad(true);
        });
    }

    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.teacherProfile = function(id){
        dataFactory.httpRequest('index.php/teachers/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.subjects = data.subjects;
            $scope.sections = data.sections;
        });
    }

    $scope.stdAttendance = function(){
        dataFactory.httpRequest('index.php/attendance/stats').then(function(data) {
            $scope.attendanceStats = data;
            $scope.changeView('stdAttendance');
            showHideLoad(true);
        });
    }

    $scope.statsAttendance = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'stdAttendance','data':$scope.form}).then(function(data) {
            if(data){
                $scope.attendanceData = data;
                $scope.changeView('stdAttendanceReport');
            }
            showHideLoad(true);
        });
    }

    $scope.statsAttendanceExport = function(exportType){
        showHideLoad();
        $scope.form.exportType = exportType;
        $http.post('reports', {'stats':'stdAttendance','data':$scope.form},{responseType: 'arraybuffer'}).success(function(data) {

            if(exportType == "excel"){
                var file = new Blob([ data ], {type : 'application/excel'});
                var fileURL = URL.createObjectURL(file);
                var a         = document.createElement('a');
                a.href        = fileURL;
                a.target      = '_blank';
                a.download    = 'StudentsAttendance.xls';
                document.body.appendChild(a);
                a.click();
            }

            if(exportType == "pdf"){
                var file = new Blob([data], {type : 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                window.open(fileURL);
            }

            showHideLoad(true);
        });
    }

    $scope.staffAttendance = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'stfAttendance','data':$scope.form}).then(function(data) {
            if(data){
                $scope.attendanceData = data;
                $scope.changeView('stfAttendanceReport');
            }
            showHideLoad(true);
        });
    }

    $scope.staffAttendanceExport = function(exportType){
        showHideLoad();
        $scope.form.exportType = exportType;
        $http.post('reports', {'stats':'stfAttendance','data':$scope.form},{responseType: 'arraybuffer'}).success(function(data) {

            if(exportType == "excel"){
                var file = new Blob([ data ], {type : 'application/excel'});
                var fileURL = URL.createObjectURL(file);
                var a         = document.createElement('a');
                a.href        = fileURL;
                a.target      = '_blank';
                a.download    = 'StaffAttendance.xls';
                document.body.appendChild(a);
                a.click();
            }

            if(exportType == "pdf"){
                var file = new Blob([data], {type : 'application/pdf'});
                var fileURL = URL.createObjectURL(file);
                window.open(fileURL);
            }

            showHideLoad(true);
        });
    }

    $scope.getVacation = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'stdVacation','data':$scope.form}).then(function(data) {
            if(data){
                $scope.vacationData = data;
                $scope.changeView('vacationList');
            }
            showHideLoad(true);
        });
    }

    $scope.removeVacation = function(id,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/vacation/delete/'+id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.vacationData.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.gettVacation = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'stfVacation','data':$scope.form}).then(function(data) {
            if(data){
                $scope.vacationData = data;
                $scope.changeView('vacationList');
            }
            showHideLoad(true);
        });
    }

    $scope.getPayments = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'payments','data':$scope.form}).then(function(data) {
            if(data){
                $scope.payments = data;
                $scope.changeView('paymentsResult');
            }
            showHideLoad(true);
        });
    }

    $scope.getExpenses = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'expenses','data':$scope.form}).then(function(data) {
            if(data){
                $scope.expenses = data;
                $scope.changeView('expensesReportsResults');
            }
            showHideLoad(true);
        });
    }

    $scope.marksheetGenerationPrepare = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/reports','POST',{},{'stats':'marksheetGenerationPrepare','data':$scope.form}).then(function(data) {
            if(data){
                $scope.classes = data.classes;
                $scope.exams = data.exams;
                $scope.changeView('marksheetGeneration');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        $scope.views.list = false;
        $scope.views.lists = false;
        $scope.views.usersStats = false;
        $scope.views.stdAttendance = false;
        $scope.views.stdAttendanceReport = false;
        $scope.views.stfAttendance = false;
        $scope.views.stfAttendanceReport = false;
        $scope.views.stVacation = false;
        $scope.views.teacherVacation = false;
        $scope.views.vacationList = false;
        $scope.views.paymentsReports = false;
        $scope.views.paymentsResult = false;
        $scope.views.marksheetGeneration = false;
        $scope.views.expensesReports = false;
        $scope.views.expensesReportsResults = false;
        $scope.views[view] = true;
    }
});

schoex.controller('gradeLevelsController', function(dataFactory,$rootScope,$scope) {
    $scope.gradeLevels = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/gradeLevels/listAll').then(function(data) {
        $scope.gradeLevels = data;
        showHideLoad(true);
    });

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/gradeLevels','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.gradeLevels.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/gradeLevels/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/gradeLevels/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.gradeLevels = apiModifyTable($scope.gradeLevels,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/gradeLevels/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.gradeLevels.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('examsListController', function(dataFactory,$rootScope,$scope,$sce) {
    $scope.examsList = {};
    $scope.classes = {};
    $scope.subjects = {};
    $scope.userRole ;
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.examSchedule = {};

    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    dataFactory.httpRequest('index.php/examsList/listAll').then(function(data) {
        $scope.examsList = data.exams;
        $scope.classes = data.classes;
        $scope.subjectsList = data.subjects;
        $scope.userRole = data.userRole;
        showHideLoad(true);
    });

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.subjects = data.subjects;
            $scope.sections = data.sections;
        });
    }

    $scope.notify = function(id){
        var confirmNotify = confirm($rootScope.phrase.sureMarks);
        if (confirmNotify == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/examsList/notify/'+id,'POST',{},$scope.form).then(function(data) {
                apiResponse(data,'add');
                showHideLoad(true);
            });
        }
    }

    $scope.addMSCol = function(){
        var colTitle = prompt("Please enter column title");
        if (colTitle != null) {
            if(typeof $scope.form.examMarksheetColumns == "undefined"){
                $scope.form.examMarksheetColumns = [];
            }

            $i = 1;
            angular.forEach($scope.form.examMarksheetColumns, function(value, key) {
                if($i <= parseInt(value.id)){
                    $i = parseInt(value.id) + 1;
                }
            });

            $scope.form.examMarksheetColumns.push({'id':$i,'title':colTitle});
        }
    }

    $scope.removeMSCol = function(col,$index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            $scope.form.examMarksheetColumns.splice($index,1);
        }
    }

    $scope.addScheduleRow = function(){
        if(typeof $scope.form.examSchedule == "undefined"){
            $scope.form.examSchedule = [];
        }
        $scope.form.examSchedule.push( {'subject':'','stDate':''} );
    }

    $scope.removeRow = function(row,index){
        $scope.form.examSchedule.splice(index,1);
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.examsList.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.examsList = apiModifyTable($scope.examsList,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/examsList/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.examsList.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.marks = function(exam){
        $scope.form.exam = exam.id;
        $scope.markClasses = [];

        try{
            exam.examClasses = JSON.parse(exam.examClasses);
        }catch(e){ }

        angular.forEach($scope.classes, function(value, key) {
            angular.forEach(exam.examClasses, function(value_) {
                if(parseInt(value.id) == parseInt(value_)){
                    $scope.markClasses.push(value);
                }
            });
        });
        $scope.changeView('premarks');
    }

    $scope.startAddMarks = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/getMarks','POST',{},$scope.form).then(function(data) {
            $scope.form.respExam = data.exam;
            $scope.form.respClass = data.class;
            $scope.form.respSubject = data.subject;
            $scope.form.respStudents = data.students;

            $scope.changeView('marks');
            showHideLoad(true);
        });
    }

    $scope.saveNewMarks = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/saveMarks/'+$scope.form.exam+"/"+$scope.form.classId+"/"+$scope.form.subjectId,'POST',{},$scope.form).then(function(data) {
            apiResponse(data,'add');
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.examDetails = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/examsList/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('examDetails');
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.premarks = false;
        $scope.views.marks = false;
        $scope.views.examDetails = false;
        $scope.views[view] = true;
    }
});

schoex.controller('eventsController', function(dataFactory,$routeParams,$rootScope,$sce,$scope) {
    $scope.events = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;

    if($routeParams.eventId){
        showHideLoad();
        dataFactory.httpRequest('index.php/events/'+$routeParams.eventId).then(function(data) {
            $scope.form = data;
            $scope.eventDescription = $sce.trustAsHtml(data.eventDescription);
            $scope.changeView('read');
            showHideLoad(true);
        });
    }else{
        dataFactory.httpRequest('index.php/events/listAll').then(function(data) {
            angular.forEach(data.events, function (item) {
                item.eventDescription = $sce.trustAsHtml(item.eventDescription);
            });
            $scope.events = data.events;
            $scope.userRole = data.userRole;
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/events/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/events/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                response.eventDescription = $sce.trustAsHtml(response.eventDescription);
                $scope.events = apiModifyTable($scope.events,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/events/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.events.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/events','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                response.eventDescription = $sce.trustAsHtml(response.eventDescription);
                $scope.events.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('materialsController', function(dataFactory,$rootScope,$scope) {
    $scope.classes = {};
    $scope.subject = {};
    $scope.materials = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;

    dataFactory.httpRequest('index.php/materials/listAll').then(function(data) {
        $scope.classes = data.classes;
        $scope.materials = data.materials;
        $scope.userRole = data.userRole
        showHideLoad(true);
    });

    $scope.numberSelected = function(item){
        var count = $(item + " :selected").length;
        if(count == 0){
            return true;
        }
    }

    $scope.sectionsList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.class_id}).then(function(data) {
            $scope.subject = data.subjects;
            $scope.sections = data.sections;
            $scope.form.subject = data.subjects;
            $scope.form.sections = data.sections;
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/materials/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.isSectionSelected = function(arrayData,valueData){
        return arrayData.indexOf(valueData) > -1;
    }

    $scope.saveEdit = function(data){
        response = apiResponse(data,'edit');
        if(data.status == "success"){
            showHideLoad();
            $scope.materials = apiModifyTable($scope.materials,response.id,response);
            $scope.changeView('list');
            showHideLoad(true);
        }
        $('#material_edit_file').val('');
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/materials/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.materials.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(data){
        response = apiResponse(data,'add');
        if(data.status == "success"){
            showHideLoad();
            $scope.materials.push(response);
            $scope.changeView('list');
            showHideLoad(true);
        }
        $('#material_add_file').val('');
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('assignmentsController', function(dataFactory,$rootScope,$scope) {
    $scope.classes = {};
    $scope.subject = {};
    $scope.section = {};
    $scope.assignments = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole ;

    dataFactory.httpRequest('index.php/assignments/listAll').then(function(data) {
        $scope.classes = data.classes;
        $scope.subject = data.subject;
        $scope.assignments = data.assignments;
        if(typeof data.assignmentsAnswers != "undefined"){
            $scope.assignmentsAnswers = data.assignmentsAnswers;
        }
        $scope.userRole = data.userRole
        showHideLoad(true);
    });

    $scope.listAnswers = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/assignments/listAnswers/'+id).then(function(data) {
            $scope.answers = data;
            $scope.changeView('answers');
            showHideLoad(true);
        });
    }

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.subject = data.subjects;
            $scope.sections = data.sections;
            $scope.form.subject = data.subjects;
            $scope.form.sections = data.sections;
        });
    }

    $scope.upload = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/assignments/checkUpload','POST',{},{'assignmentId':id}).then(function(data) {
            response = apiResponse(data,'add');
            if(data.canApply && data.canApply == "true"){
                $scope.form.assignmentId = id;
                $scope.changeView('upload');
            }
        });
        showHideLoad(true);
    }

    $scope.isSectionSelected = function(arrayData,valueData){
        return arrayData.indexOf(valueData) > -1;
    }

    $scope.saveAnswer = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            $scope.changeView('list');
            showHideLoad(true);
        }
    }

    $scope.numberSelected = function(item){
        var count = $(item + " :selected").length;
        if(count == 0){
            return true;
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/assignments/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();

            $scope.assignments = apiModifyTable($scope.assignments,response.id,response);
            $scope.changeView('list');
            showHideLoad(true);
        }
        $('#AssignEditFile').val('');
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/assignments/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.assignments.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();

            $scope.assignments.push(response);
            $scope.changeView('list');
            showHideLoad(true);
        }
        $('#AssignAddFile').val('');
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.upload = false;
        $scope.views.answers = false;
        $scope.views[view] = true;
    }
});

schoex.controller('mailsmsController', function(dataFactory,$rootScope,$scope) {
    $scope.classes = {};
    $scope.views = {};
    $scope.messages = {};
    $scope.views.send = true;
    $scope.form = {};
    $scope.form.selectedUsers = [];
    $scope.formS = {};
    $scope.sendNewScope = "form";


    $scope.getSents = function(page){
        showHideLoad();
        if(typeof page == undefined){
            var request = 'index.php/mailsms/listAll';
        }else{
            var request = 'index.php/mailsms/listAll/'+page;
        }
        dataFactory.httpRequest(request).then(function(data) {
            $scope.messages = data.items;
            $scope.totalItems = data.totalItems;
        });
    }

    dataFactory.httpRequest('index.php/classes/listAll').then(function(data) {
        $scope.classes = data.classes;
        showHideLoad(true);
    });
    $scope.getSents(1);

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/mailsms/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.messages.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.sections = data.sections;
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/mailsms','POST',{},$scope.form).then(function(data) {
            $.toast({
                heading: $rootScope.phrase.mailsms,
                text: $rootScope.phrase.mailSentSuccessfully,
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 6
            });
            $scope.messages = data.items;
            $scope.totalItems = data.totalItems;
            $scope.sendNewScope = "success";
            showHideLoad(true);
        });
    }

    $scope.linkUsers = function(usersType){
        $scope.modalTitle = $rootScope.phrase.specificUsers;
        $scope.showModalLink = !$scope.showModalLink;
        $scope.usersType = usersType;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.sureRemove);
            return;
        }
        dataFactory.httpRequest('index.php/register/searchUsers/'+$scope.usersType+'/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(userS){
        if(typeof($scope.form.selectedUsers) == "undefined"){
            $scope.form.selectedUsers = [];
        }

        $scope.form.selectedUsers.push({"student":userS.name,"role":userS.role,"id": "" + userS.id + "" });
    }

    $scope.removeUser = function(index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            for (x in $scope.form.selectedUsers) {
                if($scope.form.selectedUsers[x].id == index){
                    $scope.form.selectedUsers.splice(x,1);
                    break;
                }
            }
        }
    }

    $scope.loadTemplate = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/mailsms/templates').then(function(data) {
            $scope.templateList = data;
            $scope.modalTitle = $rootScope.phrase.loadFromTemplate;
            $scope.showModalLoad = !$scope.showModalLoad;
            showHideLoad(true);
        });
    }

    $scope.loadTemplateContent = function(){
        if($('#selectedTemplate').val() != ""){
            $scope.form.messageContentMail = $scope.templateList[$('#selectedTemplate').val()].templateMail;
            $scope.form.messageContentSms = $scope.templateList[$('#selectedTemplate').val()].templateSMS;
            $scope.showModalLoad = !$scope.showModalLoad;
        }
    }

    $scope.changeView = function(view){
        if(view == "send"){
            $scope.form = {};
            $scope.form.userType = 'teachers';
            $scope.form.sendForm = 'email';
        }
        $scope.views.send = false;
        $scope.views.list = false;
        $scope.views.settings = false;
        $scope.views[view] = true;
    }
});

schoex.controller('messagesController', function(dataFactory,$rootScope,$route,$scope,$location,$routeParams) {
    $scope.messages = {};
    $scope.message = {};
    $scope.messageDet = {};
    $scope.totalItems = 0;
    $scope.views = {};
    $scope.views.list = true;
    $scope.selectedAll = false;
    $scope.searchUsers = false;
    $scope.repeatCheck = true;
    $scope.form = {};
    $scope.messageBefore;
    $scope.messageAfter;
    $scope.searchResults = {};
    var routeData = $route.current;
    var currentMessageRefreshId;
    var messageId;

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        getResultsPage(newPage);
    };

    $scope.showMessage = function(id){
        $scope.repeatCheck = true;
        showHideLoad();
        dataFactory.httpRequest('index.php/messages/'+id).then(function(data) {
            data = successOrError(data);
            if(data){
                messageId = id;
                $scope.changeView('read');
                $scope.message = data.messages.reverse();
                $scope.messageDet = data.messageDet;
                if($scope.message[0]){
                    $scope.messageBefore = $scope.message[0].dateSent;
                }
                if($scope.message[$scope.message.length - 1]){
                    $scope.messageAfter = $scope.message[$scope.message.length - 1].dateSent;
                }
                currentMessageRefreshId = setInterval(currentMessagePull, 2000);
                $("#chat-box").slimScroll({ scrollTo: $("#chat-box").prop('scrollHeight')+'px' });
            }
            showHideLoad(true);
        });
    }

    getResultsPage(1);
    if($routeParams.messageId){
        $scope.showMessage($routeParams.messageId);
    }

    function getResultsPage(pageNumber) {
        dataFactory.httpRequest('index.php/messages/listAll/'+pageNumber).then(function(data) {
            $scope.messages = data.messages;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    $scope.linkUser = function(){
        $scope.modalTitle = $rootScope.phrase.searchUsers;
        $scope.searchUsers = !$scope.searchUsers;
    }

    $scope.searchUserButton = function(){
        var searchAbout = $('#searchKeyword').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.minCharLength3);
            return;
        }
        dataFactory.httpRequest('index.php/messages/searchUser/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(student){
        $scope.form.toId = student.username;
        $scope.searchUsers = !$scope.searchUsers;
    }


    $scope.checkAll = function(){
        $scope.selectedAll = !$scope.selectedAll;
        angular.forEach($scope.messages, function (item) {
            item.selected = $scope.selectedAll;
        });
    }

    $scope.loadOld = function(){
        dataFactory.httpRequest('index.php/messages/before/'+$scope.messageDet.fromId+'/'+$scope.messageDet.toId+'/'+$scope.messageBefore).then(function(data) {
            angular.forEach(data, function (item) {
                $scope.message.splice(0, 0,item);
            });
            if(data.length == 0){
                $('#loadOld').hide();
            }
            $scope.messageBefore = $scope.message[0].dateSent;
        });
    }

    $scope.markRead = function(){
        $scope.form.items = [];
        angular.forEach($scope.messages, function (item, key) {
            if($scope.messages[key].selected){
                $scope.form.items.push(item.id);
                $scope.messages[key].messageStatus = 0;
            }
        });
        dataFactory.httpRequest('index.php/messages/read',"POST",{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
        });
    }

    $scope.markUnRead = function(){
        $scope.form.items = [];
        angular.forEach($scope.messages, function (item, key) {
            if($scope.messages[key].selected){
                $scope.form.items.push(item.id);
                $scope.messages[key].messageStatus = 1;
            }
        });
        dataFactory.httpRequest('index.php/messages/unread',"POST",{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
        });
    }

    $scope.markDelete = function(){
        $scope.form.items = [];
        var len = $scope.messages.length
        while (len--) {
            if($scope.messages[len].selected){
                $scope.form.items.push($scope.messages[len].id);
                $scope.messages.splice(len,1);
            }
        }
        dataFactory.httpRequest('index.php/messages/delete',"POST",{},$scope.form).then(function(data) {
            response = apiResponse(data,'remove');
        });
    }

    var currentMessagePull = function(){
        if('#/messages/'+messageId == location.hash){
            dataFactory.httpRequest('index.php/messages/ajax/'+$scope.messageDet.fromId+'/'+$scope.messageDet.toId+'/'+$scope.messageAfter).then(function(data) {
                angular.forEach(data, function (item) {
                    $scope.message.push(item);
                    var newH = parseInt($("#chat-box").prop('scrollHeight')) + 100;
                    $("#chat-box").slimScroll({ scrollTo: newH+'px' });
                });
                if($scope.message[$scope.message.length - 1]){
                    $scope.messageAfter = $scope.message[$scope.message.length - 1].dateSent;
                }
            });
        }else{
            clearInterval(currentMessageRefreshId);
        }
    };

    $scope.replyMessage = function(){
        if($scope.form.reply != "" && typeof $scope.form.reply != "undefined"){
            $scope.form.disable = true;
            $scope.form.toId = $scope.messageDet.toId;
            dataFactory.httpRequest('index.php/messages/'+$scope.messageDet.id,'POST',{},$scope.form).then(function(data) {
                $("#chat-box").slimScroll({ scrollTo: $("#chat-box").prop('scrollHeight')+'px' });
                $scope.form = {};
            });
        }
    }

    $scope.sendMessageNow = function(){
        dataFactory.httpRequest('index.php/messages','POST',{},$scope.form).then(function(data) {
            $location.path('/messages/'+data.messageId);
        });
    }

    $scope.changeView = function(view){
        if(view == "read" || view == "list" || view == "create"){
            $scope.form = {};
        }
        if(view == "list" || view == "create"){
            clearInterval(currentMessageRefreshId);
        }
        $scope.views.list = false;
        $scope.views.read = false;
        $scope.views.create = false;
        $scope.views[view] = true;
    }
});

schoex.controller('onlineExamsController', function(dataFactory,$rootScope,$scope,$sce) {
    $scope.classes = {};
    $scope.subject = {};
    $scope.onlineexams = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.marksExam ;
    $scope.marks = {};
    $scope.takeData = {};
    $scope.form.examQuestion = [];
    $scope.userRole ;

    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    dataFactory.httpRequest('index.php/onlineExams/listAll').then(function(data) {
        $scope.classes = data.classes;
        $scope.subject = data.subjects;
        $scope.onlineexams = data.onlineExams;
        $scope.userRole = data.userRole;
        showHideLoad(true);
    });

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.examClass}).then(function(data) {
            $scope.subject = data.subjects;
            $scope.sections = data.sections;
        });
    }

    $scope.isSectionSelected = function(arrayData,valueData){
        if(arrayData.indexOf(valueData.toString()) > -1 || arrayData.indexOf(parseInt(valueData)) > -1){
            return true;
        }
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/onlineExams/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.onlineexams.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/onlineExams','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.onlineexams.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/onlineExams/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            $scope.subject = $scope.form.subject;
            $scope.sections = data.sections;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        console.log($scope.form);
        dataFactory.httpRequest('index.php/onlineExams/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.onlineexams = apiModifyTable($scope.onlineexams,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.addQuestion = function(){
        var hasTrueAnswer = false;
        if (typeof $scope.examTitle === "undefined" || $scope.examTitle == "") {
            alert("Question Title undefined");
            return ;
        }

        var questionData = {};
        questionData.title = $scope.examTitle;
        questionData.type = $scope.examQType;

        if (typeof $scope.ans1 != "undefined" && $scope.ans1 != "") {
            questionData.ans1 = $scope.ans1;
            if(questionData.type == "text"){
                hasTrueAnswer = true;
            }
        }
        if (typeof $scope.ans2 != "undefined" && $scope.ans2 != "") {
            questionData.ans2 = $scope.ans2;
        }
        if (typeof $scope.ans3 != "undefined" && $scope.ans3 != "") {
            questionData.ans3 = $scope.ans3;
        }
        if (typeof $scope.ans4 != "undefined" && $scope.ans4 != "") {
            questionData.ans4 = $scope.ans4;
        }
        if (typeof $scope.Tans != "undefined" && $scope.Tans != "") {
            questionData.Tans = $scope.Tans;
            hasTrueAnswer = true;
        }
        if (typeof $scope.Tans1 != "undefined" && $scope.Tans1 != "") {
            questionData.Tans1 = $scope.Tans1;
            hasTrueAnswer = true;
        }
        if (typeof $scope.Tans2 != "undefined" && $scope.Tans2 != "") {
            questionData.Tans2 = $scope.Tans2;
            hasTrueAnswer = true;
        }
        if (typeof $scope.Tans3 != "undefined" && $scope.Tans3 != "") {
            questionData.Tans3 = $scope.Tans3;
            hasTrueAnswer = true;
        }
        if (typeof $scope.Tans4 != "undefined" && $scope.Tans4 != "") {
            questionData.Tans4 = $scope.Tans4;
            hasTrueAnswer = true;
        }
        if( hasTrueAnswer == false){
            alert("You must select the true answer");
            return;
        }

        if (typeof $scope.questionMark != "undefined" && $scope.questionMark != "") {
            questionData.questionMark = $scope.questionMark;
        }

        $scope.form.examQuestion.push(questionData);
        console.log($scope.form.examQuestion);

        $scope.examTitle = "";
        $scope.questionMark = "";
        $scope.ans1 = "";
        $scope.ans2 = "";
        $scope.ans3 = "";
        $scope.ans4 = "";
        $scope.Tans = "";
        $scope.Tans1 = "";
        $scope.Tans2 = "";
        $scope.Tans3 = "";
        $scope.Tans4 = "";
    }

    $scope.removeQuestion = function(index){
        $scope.form.examQuestion.splice(index,1);
    }

    $scope.take = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/onlineExams/take/'+id,'POST',{},{}).then(function(data) {
            response = apiResponse(data,'add');
            if(response){
                $scope.changeView('take');
                $scope.takeData = data;
                document.getElementById('onlineExamTimer').start();
                if(data.timeLeft != 0){
                    $scope.$broadcast('timer-set-countdown', data.timeLeft);
                }
            }
        });
        showHideLoad(true);
    }

    $scope.finishExam = function(){
        $scope.submitExam();
        alert($rootScope.phrase.examTimedOut);
    }

    $scope.submitExam = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/onlineExams/took/'+$scope.takeData.id,'POST',{},$scope.takeData).then(function(data) {
            if (typeof data.grade != "undefined") {
                alert($rootScope.phrase.examYourGrade+data.grade);
            }else{
                alert($rootScope.phrase.examSubmitionSaved);
            }
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.marksData = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/onlineExams/marks/'+id).then(function(data) {
            $scope.marks = data.grade;
            $scope.examDegreeSuccess = data.examDegreeSuccess;

            $scope.marksExam = id;
            $scope.changeView('marks');
            showHideLoad(true);
        });
    }

    $scope.isSuccess = function(pass,grade){
        if(typeof grade == null){
            return ;
        }
        if(parseInt(grade) >= parseInt(pass)){
            return 'success';
        }
        if(parseInt(grade) < parseInt(pass)){
            return 'failed';
        }
    }

    $scope.uploadQimage = function($index,question){
        $scope.modalTitle = "Upload Image for question";
        $scope.uploadQimageModal = !$scope.uploadQimageModal;
        $scope.uploadImageQ = {};
        $scope.uploadImageQ.id = $index;
        $scope.uploadImageQ.question = question;
    }

    $scope.saveUploadImage = function(content){
        $scope.uploadQimageModal = !$scope.uploadQimageModal;
        $scope.form.examQuestion[$scope.uploadImageQ.id].image = content;
    }

    $scope.showStdMarks = function(studentAnswers){
        var isLegacy = false;
        $scope.studentAnswers = JSON.parse(studentAnswers);
        angular.forEach($scope.studentAnswers, function (item) {
            if(typeof item.state == "undefined" ){
                isLegacy = true;
            }
        });
        if(isLegacy == true){
            alert("Student answers not defined");
        }else{
            $scope.modalTitle = "Student's answers";
            $scope.showstdAnswerModal = !$scope.showstdAnswerModal;
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
            $scope.form.examQuestion = [];
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.take = false;
        $scope.views.marks = false;
        $scope.views[view] = true;
    }
});

schoex.controller('TransportsController', function(dataFactory,$scope,$rootScope) {
    $scope.transports = {};
    $scope.transportsList = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.userRole = $rootScope.dashboardData.role;

    dataFactory.httpRequest('index.php/transports/listAll').then(function(data) {
        $scope.transports = data;
        showHideLoad(true);
    });

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/transports/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/transports/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.transports = apiModifyTable($scope.transports,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/transports/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.transports.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/transports','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.transports.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.list = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/transports/list/'+id).then(function(data) {
            $scope.changeView('listSubs');
            $scope.transportsList = data;
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.listSubs = false;
        $scope.views[view] = true;
    }
});

schoex.controller('mediaController', function($rootScope,dataFactory,$scope) {
    $scope.albums = {};
    $scope.media = {};
    $scope.dirParent = -1;
    $scope.dirNow = 0;
    $scope.current = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.form = {};

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.addAlbum = false;
        $scope.views.editAlbum = false;
        $scope.views.addMedia = false;
        $scope.views.editMedia = false;
        $scope.views[view] = true;
    }

    $scope.loadAlbum = function(id){
        showHideLoad();
        if(typeof id == "undefined" || id == 0){
            var reqUrl = 'index.php/media/listAll';
        }else{
            var reqUrl = 'index.php/media/listAll/'+id;
        }
        dataFactory.httpRequest(reqUrl).then(function(data) {
            $scope.albums = data.albums;
            $scope.media = data.media;
            if(data.current){
                $scope.current = data.current;
                $scope.dirParent = data.current.albumParent;
                $scope.dirNow = id;
            }else{
                $scope.current = {};
                $scope.dirParent = -1;
                $scope.dirNow = 0;
            }
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.loadAlbum();

    $scope.saveAlbum = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();

            $scope.albums.push(response);
            $scope.loadAlbum($scope.dirNow);
        }
        showHideLoad(true);
    }

    $scope.removeAlbum = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.removeAlbum);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/media/album/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.albums.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.editAlbumData = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/media/editAlbum/'+id).then(function(data) {
            $scope.changeView('editAlbum');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEditAlbum = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();

            $scope.albums = apiModifyTable($scope.albums,response.id,response);
            $scope.loadAlbum($scope.dirNow);
        }
        showHideLoad(true);
    }

    $scope.saveMedia = function(content){
        response = apiResponse(content,'add');
        if(content.status == "success"){
            showHideLoad();

            $scope.media.push(response);
            $scope.loadAlbum($scope.dirNow);
        }
        showHideLoad(true);
    }

    $scope.editItem = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/media/'+id).then(function(data) {
            $scope.changeView('editMedia');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEditItem = function(content){
        response = apiResponse(content,'edit');
        if(content.status == "success"){
            showHideLoad();

            $scope.media = apiModifyTable($scope.media,response.id,response);
            $scope.loadAlbum($scope.dirNow);
        }
        showHideLoad(true);
    }

    $scope.removeItem = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/media/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.media.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

});

schoex.controller('staticController', function(dataFactory,$routeParams,$scope,$sce,$rootScope) {
    $scope.staticPages = {};
    $scope.views = {};
    $scope.form = {};
    $scope.userRole = $rootScope.dashboardData.role;
    $scope.pageId = $routeParams.pageId;

    if (typeof $scope.pageId != "undefined" && $scope.pageId != "") {
        showHideLoad();
        dataFactory.httpRequest('index.php/static/'+$scope.pageId).then(function(data) {
            $scope.changeView('show');
            $scope.form.pageTitle = data.pageTitle;
            $scope.pageContent = $sce.trustAsHtml(data.pageContent);
            showHideLoad(true);
        });
    }else{
        dataFactory.httpRequest('index.php/static/listAll').then(function(data) {
            $scope.staticPages = data;
            $scope.changeView('list');
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/static','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.staticPages.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
        $scope.form = {};
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/static/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/static/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.staticPages = apiModifyTable($scope.staticPages,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
        $scope.form = {};
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/static/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.staticPages.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.pageActive = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/static/active/'+id).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                angular.forEach($scope.staticPages, function (item) {
                    if(item.id == response.id){
                        if(item.pageActive == 1){
                            item.pageActive = 0;
                        }else{
                            item.pageActive = 1;
                        }
                    }
                });
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.show = false;
        $scope.views.listSubs = false;
        $scope.views[view] = true;
    }
});

schoex.controller('attendanceStatsController', function(dataFactory,$scope,$sce) {
    $scope.attendanceStats = {};
    $scope.attendanceData = {};
    $scope.userRole;
    $scope.views = {};
    $scope.form = {};

    dataFactory.httpRequest('index.php/attendance/stats').then(function(data) {
        $scope.attendanceStats = data;
        if(data.role == "student"){
            $scope.changeView('lists');
        }else if(data.role == "parent"){
            $scope.changeView('listp');
        }
        $scope.userRole = data.attendanceModel;
        showHideLoad(true);
    });

    $scope.statsAttendance = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/attendance/stats','POST',{},$scope.form).then(function(data) {
            if(data){
                $scope.attendanceData = data;
                $scope.changeView('listdata');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.listdata = false;
        $scope.views.lists = false;
        $scope.views.listp = false;
        $scope.views[view] = true;
    }
});

schoex.controller('pollsController', function(dataFactory,$scope,$rootScope) {
    $scope.polls = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/polls/listAll').then(function(data) {
        $scope.polls = data;
        showHideLoad(true);
    });

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/polls/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.polls.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.addPollOption = function(item){
        var optionTitle = prompt($rootScope.phrase.voteOptionTitle);
        if (optionTitle != null) {
            if (typeof $scope.form.pollOptions === "undefined" || $scope.form.pollOptions == "") {
                $scope.form.pollOptions = [];
            }
            var newOption = {'title':optionTitle};
            $scope.form.pollOptions.push(newOption);
        }
    }

    $scope.removePollOption = function(item,index){
        $scope.form.pollOptions.splice(index,1);
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/polls/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/polls/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.polls = apiModifyTable($scope.polls,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/polls','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.polls.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.makeActive = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/polls/active/'+id,'POST',{}).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                angular.forEach($scope.polls, function (item) {
                    item.pollStatus = 0;
                    if(item.id == response.id){
                        item.pollStatus = 1;
                    }
                });
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('mailsmsTemplatesController', function(dataFactory,$scope,$rootScope) {
    $scope.templates = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/MailSMSTemplates/listAll').then(function(data) {
        $scope.templates = data;
        showHideLoad(true);
    });

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/MailSMSTemplates/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/MailSMSTemplates/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/MailSMSTemplates','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.templates.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/MailSMSTemplates/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.templates.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('dormitoriesController', function(dataFactory,$rootScope,$scope) {
    $scope.dormitories = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/dormitories/listAll').then(function(data) {
        $scope.dormitories = data;
        showHideLoad(true);
    });

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/dormitories/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/dormitories/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.dormitories = apiModifyTable($scope.dormitories,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/dormitories/delete/'+item.id,'POST',{},$scope.form).then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.dormitories.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/dormitories','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.dormitories.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('invoicesController', function(dataFactory,$scope,$sce,$rootScope,$route) {
    $scope.invoices = {};
    $scope.students = {};
    $scope.classes = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.invoice = {};
    $scope.payDetails = {};
    $scope.searchInput = {};
    $scope.userRole = $rootScope.dashboardData.role;
    var methodName = $route.current.methodName;

    $scope.listInvoices = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/listAll/'+pageNumber).then(function(data) {
            $scope.invoices = data.invoices;
            $scope.students = data.students;
            $scope.classes = data.classes;
            $scope.totalItems = data.totalItems;
            $scope.currency_symbol = data.currency_symbol;
            showHideLoad(true);
        });
    }

    $scope.searchDB = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/listAll/'+pageNumber,'POST',{},{'searchInput':$scope.searchInput}).then(function(data) {
            $scope.invoices = data.invoices;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    if(methodName == "dueinvoices"){
        $scope.searchInput.dueInv = true;
    }

    $scope.getResultsPage = function(id){
        if(methodName == "dueinvoices"){
            $scope.searchDB(id);
        }else if ( !jQuery.isEmptyObject($scope.searchInput) ) {
            $scope.searchDB(id);
        }else{
            $scope.listInvoices(id);
        }
    }

    $scope.getResultsPage(1);

    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.toggleSearch = function(){
        $('.advSearch').toggleClass('col-0 col-3 hidden',1000);
        $('.listContent').toggleClass('col-12 col-9',1000);
    }

    $scope.resetSearch = function(){
        $scope.searchInput = {};
        $scope.getResultsPage(1);
    }

    $scope.linkStudent = function(){
        $scope.modalTitle = $rootScope.phrase.selectStudents;
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.minCharLength3);
            return;
        }
        dataFactory.httpRequest('index.php/invoices/searchUsers/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(student){
        if(!$scope.form.paymentStudent){
            $scope.form.paymentStudent = [];
        }
        console.log($scope.form.paymentStudent);
        $scope.form.paymentStudent.push({'id':student.id,'name':student.name});
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.removeStudent = function(index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            $scope.form.paymentStudent.splice(index,1);
        }
    }

    $scope.totalItems = 0;
    $scope.pageChanged = function(newPage) {
        $scope.getResultsPage(newPage);
    };

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/invoices/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.invoices.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                angular.forEach(response, function (item) {
                    $scope.invoices.push(item);
                });
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/'+id).then(function(data) {
            $scope.form = data;
            $scope.changeView('edit');
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.invoices = apiModifyTable($scope.invoices,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.seeInvoice = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/invoice/'+id).then(function(data) {
            $scope.invoice = data;
            $scope.changeView('invoice');
            showHideLoad(true);
        });
    }

    $scope.alertPaidData = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/details/'+id).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.payDetails = response.data;
                $scope.changeView('details');
            }
            showHideLoad(true);
        });
    }

    $scope.addPaymentRow = function(){
        if(typeof($scope.form.paymentRows) == "undefined"){
            $scope.form.paymentRows = [];
        }
        $scope.form.paymentRows.push({'title':'','amount':''});
    }

    $scope.recalcTotalAmount = function(){
        $scope.form.paymentAmount = 0;
        angular.forEach($scope.form.paymentRows, function(value, key) {
            $scope.form.paymentAmount = parseInt($scope.form.paymentAmount) + parseInt(value.amount);
        });
    }

    $scope.removeRow = function(row,index){
        $scope.form.paymentRows.splice(index,1);
        $scope.recalcTotalAmount();
    }

    $scope.collect = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/invoice/'+id).then(function(data) {
            $scope.invoice = data;
            $scope.modalTitle = "Collect Invoice";
            $scope.modalClass = "modal-lg";
            $scope.collectInvoice = !$scope.collectInvoice;
            showHideLoad(true);
        });
    }

    $scope.collectInvoiceNow = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/collect/'+id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.collectInvoice = !$scope.collectInvoice;
                if($scope.views.invoice){
                    $scope.seeInvoice(id);
                }else {
                    $scope.invoices = apiModifyTable($scope.invoices,response.id,response);
                }
            }
            showHideLoad(true);
        });
    }

    $scope.revert = function(collection){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/invoices/revert/'+collection,'POST',{},{}).then(function(data) {
                response = apiResponse(data,'edit');
                if(data.status == "success"){
                    $scope.seeInvoice($scope.invoice.payment.id);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.payOnline = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/invoices/invoice/'+id).then(function(data) {
            $scope.invoice = data;
            $scope.modalTitle = "Pay Invoice Online";
            $scope.payOnlineModal = !$scope.payOnlineModal;
            showHideLoad(true);
        });
    }

    $scope.payOnlineNow = function(id){
        $scope.form.invoice = id;
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.invoice = false;
        $scope.views.details = false;
        $scope.views[view] = true;
    }
});

schoex.controller('languagesController', function(dataFactory,$rootScope,$scope) {
    $scope.languages = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    $scope.translate = function(){
        $(".phraseList label").each(function(i, current){
            var str = $(current).text();
            if($(current).children('input').val() == ""){
                var str2 = $(current).children('input').val(str);
                $(current).children('input').trigger('input');
            }

        });
        return;
    }

    dataFactory.httpRequest('index.php/languages/listAll').then(function(data) {
        $scope.languages = data;
        showHideLoad(true);
    });

    $scope.addLang = function(){
        $scope.form = {};
        $scope.form.languagePhrases = {};
        $scope.changeView('edit');
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/languages/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        if(typeof $scope.form.id == "undefined"){
            showHideLoad();
            dataFactory.httpRequest('index.php/languages','POST',{},$scope.form).then(function(data) {
                response = apiResponse(data,'add');
                if(data.status == "success"){
                    $scope.languages.push(response);
                    $scope.changeView('list');
                }
                showHideLoad(true);
            });
        }else{
            showHideLoad();
            dataFactory.httpRequest('index.php/languages/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
                response = apiResponse(data,'edit');
                if(data.status == "success"){
                    $scope.languages = apiModifyTable($scope.languages,response.id,response);
                    $scope.changeView('list');
                }
                showHideLoad(true);
            });
        }
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/languages/delete/'+item.id,'POST',{},$scope.form).then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.languages.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.saveAdd = function(){

    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('promotionController', function(dataFactory,$rootScope,$scope) {
    $scope.classes = {};
    $scope.students = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.sections ={};
    $scope.classesArray = [];
    $scope.form.studentInfo = [];

    showHideLoad(true);
    $scope.classesList = function(){
        dataFactory.httpRequest('index.php/dashboard/classesList','POST',{},{"academicYear":$scope.form.acYear}).then(function(data) {
            $scope.classes = data.classes;
            $scope.subjects = data.subjects;
        });
    }

    $scope.classesPromoteList = function(key){
        dataFactory.httpRequest('index.php/dashboard/classesList','POST',{},{"academicYear":$scope.studentsList.students[key].acYear}).then(function(data) {
            $scope.classesArray[key] = data;
            $scope.sections = data.sections;
        });
    }


    $scope.listStudents = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/promotion/listStudents','POST',{},$scope.form).then(function(data) {
            $scope.promoType = $scope.form.promoType;
            $scope.studentsList = data;
            $scope.sections = data.classes.sections;

            angular.forEach(data.students, function(value, key) {
                $scope.classesArray[key] = data.classes;
            });

            $scope.changeView('studentPromote');
            showHideLoad(true);
        });
    }

    $scope.removePromoStudent = function(index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            for (key in $scope.studentsList.students) {
                if($scope.studentsList.students[key].id == index){
                    delete $scope.studentsList.students[key];
                    break;
                }
            }
        }
    }

    $scope.promoteNow = function(){
        showHideLoad();
        if($scope.promoType == 'graduate'){
            angular.forEach($scope.studentsList.students, function(value, key) {
                $scope.studentsList.students[key]['acYear'] = 0;
            });
        }
        dataFactory.httpRequest('index.php/promotion','POST',{},{'promote':$scope.studentsList.students,'promoType':$scope.promoType}).then(function(data) {
            if(data){
                $scope.studentsPromoted = data;
                $scope.changeView('studentsPromoted');
            }
            showHideLoad(true);
        });
    }

    $scope.linkStudent = function(){
        $scope.modalTitle = $rootScope.phrase.promoteStudents;
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.minCharLength3);
            return;
        }
        dataFactory.httpRequest('index.php/promotion/search/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(student){
        $scope.form.studentInfo.push({"student":student.name,"id": "" + student.id + "" });
    }

    $scope.removeStudent = function(index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            for (x in $scope.form.studentInfo) {
                if($scope.form.studentInfo[x].id == index){
                    $scope.form.studentInfo.splice(x,1);
                    break;
                }
            }
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.studentsPromoted = false;
        $scope.views.studentPromote = false;
        $scope.views[view] = true;
    }
});

schoex.controller('academicYearController', function(dataFactory,$rootScope,$scope) {
    $scope.academicYears = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/academic/listAll').then(function(data) {
        $scope.academicYears = data;
        showHideLoad(true);
    });

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/academic/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.academicYears.splice(index,1);
                    $rootScope.dashboardData.academicYear = $scope.academicYears;
                }
                showHideLoad(true);
            });
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/academic/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/academic/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(response){
                $scope.academicYears = apiModifyTable($scope.academicYears,response.id,response);
                $rootScope.dashboardData.academicYear = $scope.academicYears;
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/academic','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(response){
                if(response.isDefault == 1){
                    angular.forEach($scope.academicYears, function (item) {
                        item.isDefault = 0;
                    });
                }
                $scope.academicYears.push({"id":response.id,"yearTitle":response.yearTitle,"isDefault":response.isDefault});
                $rootScope.dashboardData.academicYear = $scope.academicYears;
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.makeActive = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/academic/active/'+id,'POST',{}).then(function(data) {
            response = apiResponse(data,'edit');
            if(response){
                angular.forEach($scope.academicYears, function (item) {
                    item.isDefault = 0;
                    if(item.id == response.id){
                        item.isDefault = 1;
                    }
                });
                $rootScope.dashboardData.academicYear = $scope.academicYears;
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('vacationController', function(dataFactory,$rootScope,$scope) {
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.vacation ;

    showHideLoad(true);
    $scope.getVacation = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/vacation','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.vacation = response;
                $scope.changeView('lists');
            }
            showHideLoad(true);
        });
    }

    $scope.confirmVacation = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/vacation/confirm','POST',{},{'days':$scope.vacation}).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.lists = false;
        $scope.views[view] = true;
    }
});

schoex.controller('hostelController', function(dataFactory,$rootScope,$scope) {
    $scope.hostelList = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.hostelSubList = {};
    $scope.form = {};

    dataFactory.httpRequest('index.php/hostel/listAll').then(function(data) {
        $scope.hostelList = data;
        showHideLoad(true);
    });

    $scope.listSub = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/hostel/listSubs/'+id).then(function(data) {
            $scope.changeView('listSubs');
            $scope.hostelSubList = data;
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/hostel','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.hostelList.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/hostel/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/hostel/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.hostelList = apiModifyTable($scope.hostelList,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/hostel/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.hostelList.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views.listSubs = false;
        $scope.views[view] = true;
    }
});

schoex.controller('hostelCatController', function(dataFactory,$rootScope,$scope) {
    $scope.hostelList = {};
    $scope.hostelCat = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/hostelCat/listAll').then(function(data) {
        $scope.hostelList = data.hostel;
        $scope.hostelCat = data.cat;
        showHideLoad(true);
    });

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/hostelCat','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.hostelCat.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/hostelCat/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/hostelCat/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.hostelCat = apiModifyTable($scope.hostelCat,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/hostelCat/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.hostelCat.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('expensesController', function(dataFactory,$rootScope,$scope,$route) {
    $scope.expenses = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    $scope.listInvoices = function(pageNumber){
        showHideLoad();
        dataFactory.httpRequest('index.php/expenses/listAll/'+pageNumber).then(function(data) {
            $scope.expenses = data.expenses;
            $scope.totalItems = data.totalItems;
            $scope.expenses_cat = data.expenses_cat;
            showHideLoad(true);
        });
    }

    $scope.listInvoices(1);

    $scope.getTotal = function(key){
        var total = 0;
        for(var i = 0; i < $scope.expenses[key].length; i++){
            total += parseInt($scope.expenses[key][i].expenseAmount);
        }
        return total;
    }

    $scope.saveAdd = function(data){
        showHideLoad();
        response = apiResponse(data,'add');
        if(data.status == "success"){
            $route.reload();
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/expenses/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(data){
        showHideLoad();
        response = apiResponse(data,'edit');
        if(data.status == "success"){
            $route.reload();
            $scope.changeView('list');
        }
        showHideLoad(true);
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/expenses/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $route.reload();
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('feeTypeController', function(dataFactory,$rootScope,$scope) {
    $scope.feeTypes = {};
    $scope.feeGroups = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/feeTypes/listAll').then(function(data) {
        $scope.feeTypes = data.types;
        $scope.feeGroups = data.groups;
        showHideLoad(true);
    });

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeTypes','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.feeTypes.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeTypes/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeTypes/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.feeTypes = apiModifyTable($scope.feeTypes,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/feeTypes/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.feeTypes.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.chgFeeSchType = function(location){
        $scope.form.feeSchDetails = {};
        if(location == "first"){
            $scope.form.feeSchDetails.first = {};
        }
        if(location == "second"){
            $scope.form.feeSchDetails.first = {};
            $scope.form.feeSchDetails.second = {};
        }
        if(location == "third"){
            $scope.form.feeSchDetails.first = {};
            $scope.form.feeSchDetails.second = {};
            $scope.form.feeSchDetails.third = {};
        }
        if(location == "fourth"){
            $scope.form.feeSchDetails.first = {};
            $scope.form.feeSchDetails.second = {};
            $scope.form.feeSchDetails.third = {};
            $scope.form.feeSchDetails.fourth = {};
        }
        if(location == "twelvth"){
            $scope.form.feeSchDetails.first = {};
            $scope.form.feeSchDetails.second = {};
            $scope.form.feeSchDetails.third = {};
            $scope.form.feeSchDetails.fourth = {};
            $scope.form.feeSchDetails.fifth = {};
            $scope.form.feeSchDetails.sixth = {};
            $scope.form.feeSchDetails.seventh = {};
            $scope.form.feeSchDetails.eighth = {};
            $scope.form.feeSchDetails.ninth = {};
            $scope.form.feeSchDetails.tenth = {};
            $scope.form.feeSchDetails.eleventh = {};
            $scope.form.feeSchDetails.twelveth = {};
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('feeGroupController', function(dataFactory,$rootScope,$scope) {
    $scope.feeGroups = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/feeGroups/listAll').then(function(data) {
        $scope.feeGroups = data;
        showHideLoad(true);
    });

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeGroups','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.feeGroups.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeGroups/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeGroups/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.feeGroups = apiModifyTable($scope.feeGroups,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/feeGroups/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.feeGroups.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('expensesCatController', function(dataFactory,$rootScope,$scope) {
    $scope.expensesCats = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/expensesCat/listAll').then(function(data) {
        $scope.expensesCats = data;
        showHideLoad(true);
    });

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/expensesCat','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $scope.expensesCats.push(response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/expensesCat/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/expensesCat/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $scope.expensesCats = apiModifyTable($scope.expensesCats,response.id,response);
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/expensesCat/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.expensesCats.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('feeAllocationController', function(dataFactory,$rootScope,$scope,$route) {
    $scope.classes = {};
    $scope.feeTypes = {};
    $scope.classAllocation = {};
    $scope.studentAllocation = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/feeAllocation/listAll').then(function(data) {
        $scope.feeGroups = data.feeGroups;
        $scope.classes = data.classes;
        $scope.feeAllocation = data.feeAllocation;
        showHideLoad(true);
    });

    $scope.loadFeeTypes = function(){
        dataFactory.httpRequest('index.php/feeAllocation/listFeeTypes/'+$scope.form.feeGroup).then(function(data) {
            $scope.feeTypes = data;
        });
    }

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.feeSchDetailsClass}).then(function(data) {
            $scope.sections = data.sections;
        });
    }

    $scope.isSectionSelected = function(arrayData,valueData){
        return arrayData.indexOf(valueData) > -1;
    }

    $scope.showModal = false;
    $scope.studentProfile = function(id){
        dataFactory.httpRequest('index.php/students/profile/'+id).then(function(data) {
            $scope.modalTitle = data.title;
            $scope.modalContent = $sce.trustAsHtml(data.content);
            $scope.showModal = !$scope.showModal;
        });
    };

    $scope.linkStudent = function(){
        $scope.modalTitle = $rootScope.phrase.selectStudents;
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.minCharLength3);
            return;
        }
        dataFactory.httpRequest('index.php/invoices/searchUsers/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(student){
        if(!$scope.form.feeSchDetailsStudents){
            $scope.form.feeSchDetailsStudents = [];
        }
        $scope.form.feeSchDetailsStudents.push({'id':student.id,'name':student.name});
        $scope.showModalLink = !$scope.showModalLink;
    }

    $scope.removeStudent = function(index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            for (x in $scope.form.feeSchDetailsStudents) {
                if($scope.form.feeSchDetailsStudents[x].id == index){

                    $scope.form.feeSchDetailsStudents.splice(x,1);
                    $scope.form.studentInfoSer = JSON.stringify($scope.form.feeSchDetailsStudents);
                    break;
                }
            }
        }
    }

    $scope.addFeeAllocation = function(){
        $scope.changeView('add');
        $scope.form.allocationValues = $scope.feeTypes;
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeAllocation','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $route.reload();
            }
            showHideLoad(true);
        });
    }

    $scope.feeType = function(id){
        for (x in $scope.feeTypes) {
            if($scope.feeTypes[x].id == id){
                return $scope.feeTypes[x].feeTitle;
            }
        }
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeAllocation/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data.allocation;
            $scope.feeTypes = data.feeTypes;
            $scope.sections = data.sections;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/feeAllocation/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $route.reload();
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index,rtype){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/feeAllocation/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    if(rtype == "class"){
                        $scope.classAllocation.splice(index,1);
                    }else{
                        $scope.studentAllocation.splice(index,1);
                    }
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('sectionsController', function(dataFactory,$rootScope,$scope,$route) {
    $scope.sections = {};
    $scope.classes = {};
    $scope.teachers = {};
    $scope.views = {};
    $scope.views.list = true;
    $scope.form = {};

    dataFactory.httpRequest('index.php/sections/listAll').then(function(data) {
        $scope.sections = data.sections;
        $scope.classes = data.classes;
        $scope.teachers = data.teachers;
        showHideLoad(true);
    });

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/sections','POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'add');
            if(data.status == "success"){
                $route.reload();
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.edit = function(id){
        showHideLoad();
        dataFactory.httpRequest('index.php/sections/'+id).then(function(data) {
            $scope.changeView('edit');
            $scope.form = data;
            showHideLoad(true);
        });
    }

    $scope.saveEdit = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/sections/'+$scope.form.id,'POST',{},$scope.form).then(function(data) {
            response = apiResponse(data,'edit');
            if(data.status == "success"){
                $route.reload();
                $scope.changeView('list');
            }
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/sections/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $route.reload();
                }
                showHideLoad(true);
            });
        }
    }

    $scope.changeView = function(view){
        if(view == "add" || view == "list" || view == "show"){
            $scope.form = {};
        }
        $scope.views.list = false;
        $scope.views.add = false;
        $scope.views.edit = false;
        $scope.views[view] = true;
    }
});

schoex.controller('mobileNotifController', function(dataFactory,$rootScope,$scope) {
    $scope.classes = {};
    $scope.views = {};
    $scope.messages = {};
    $scope.views.list = true;
    $scope.form = {};
    $scope.form.selectedUsers = [];
    $scope.formS = {};
    $scope.sendNewScope = "form";

    $scope.loadNotifications = function(page){
        dataFactory.httpRequest('index.php/mobileNotif/listAll/' + page).then(function(data) {
            $scope.subject_list = data.subject_list;
            $scope.messages = data.items;
            $scope.totalItems = data.totalItems;
            showHideLoad(true);
        });
    }

    dataFactory.httpRequest('index.php/classes/listAll').then(function(data) {
        $scope.classes = data.classes;
    });

    $scope.loadNotifications(1);

    $scope.subjectList = function(){
        dataFactory.httpRequest('index.php/dashboard/sectionsSubjectsList','POST',{},{"classes":$scope.form.classId}).then(function(data) {
            $scope.sections = data.sections;
        });
    }

    $scope.sendNotif = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/classes/listAll').then(function(data) {
            $scope.classes = data.classes;
            $scope.changeView('send');
            showHideLoad(true);
        });
    }

    $scope.saveAdd = function(){
        showHideLoad();
        dataFactory.httpRequest('index.php/mobileNotif','POST',{},$scope.form).then(function(data) {
            $.toast({
                heading: $rootScope.phrase.mobileNotifications,
                text: $rootScope.phrase.messQueued,
                position: 'top-right',
                loaderBg:'#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 6
            });
            $scope.messages = data.items;
            $scope.sendNewScope = "success";
            showHideLoad(true);
        });
    }

    $scope.remove = function(item,index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            showHideLoad();
            dataFactory.httpRequest('index.php/mobileNotif/delete/'+item.id,'POST').then(function(data) {
                response = apiResponse(data,'remove');
                if(data.status == "success"){
                    $scope.messages.splice(index,1);
                }
                showHideLoad(true);
            });
        }
    }

    $scope.linkUsers = function(usersType){
        $scope.modalTitle = $rootScope.phrase.specificUsers;
        $scope.showModalLink = !$scope.showModalLink;
        $scope.usersType = usersType;
    }

    $scope.linkStudentButton = function(){
        var searchAbout = $('#searchLink').val();
        if(searchAbout.length < 3){
            alert($rootScope.phrase.sureRemove);
            return;
        }
        dataFactory.httpRequest('index.php/register/searchUsers/'+$scope.usersType+'/'+searchAbout).then(function(data) {
            $scope.searchResults = data;
        });
    }

    $scope.linkStudentFinish = function(userS){
        if(typeof($scope.form.selectedUsers) == "undefined"){
            $scope.form.selectedUsers = [];
        }

        $scope.form.selectedUsers.push({"student":userS.name,"role":userS.role,"id": "" + userS.id + "" });
    }

    $scope.removeUser = function(index){
        var confirmRemove = confirm($rootScope.phrase.sureRemove);
        if (confirmRemove == true) {
            for (x in $scope.form.selectedUsers) {
                if($scope.form.selectedUsers[x].id == index){
                    $scope.form.selectedUsers.splice(x,1);
                    break;
                }
            }
        }
    }

    $scope.changeView = function(view){
        if(view == "send"){
            $scope.form = {};
        }
        $scope.views.send = false;
        $scope.views.list = false;
        $scope.views.settings = false;
        $scope.views[view] = true;
    }

});
