schoex.config(function($routeProvider,$locationProvider) {

    $routeProvider.when('/', {
        templateUrl : 'assets/templates/home.html',
        controller  : 'dashboardController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/dormitories', {
        templateUrl : 'assets/templates/dormitories.html',
        controller  : 'dormitoriesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/admins', {
        templateUrl : 'assets/templates/admins.html',
        controller  : 'adminsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/accountants', {
        templateUrl : 'assets/templates/accountants.html',
        controller  : 'accountantsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/teachers', {
        templateUrl : 'assets/templates/teachers.html',
        controller  : 'teachersController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/students', {
        templateUrl : 'assets/templates/students.html',
        controller  : 'studentsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/students/marksheet', {
        templateUrl : 'assets/templates/students.html',
        controller  : 'studentsController',
        methodName: 'marksheet',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/parents', {
        templateUrl : 'assets/templates/stparents.html',
        controller  : 'parentsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/hostel', {
        templateUrl : 'assets/templates/hostel.html',
        controller  : 'hostelController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/hostelCat', {
        templateUrl : 'assets/templates/hostelCat.html',
        controller  : 'hostelCatController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/classes', {
        templateUrl : 'assets/templates/classes.html',
        controller  : 'classesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/sections', {
        templateUrl : 'assets/templates/sections.html',
        controller  : 'sectionsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/subjects', {
        templateUrl : 'assets/templates/subjects.html',
        controller  : 'subjectsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/newsboard', {
        templateUrl : 'assets/templates/newsboard.html',
        controller  : 'newsboardController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/newsboard/:newsId', {
        templateUrl : 'assets/templates/newsboard.html',
        controller  : 'newsboardController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/library', {
        templateUrl : 'assets/templates/library.html',
        controller  : 'libraryController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/account', {
        templateUrl : 'assets/templates/accountSettings.html',
        controller  : 'accountSettingsController',
        methodName: 'profile',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/account/email', {
        templateUrl : 'assets/templates/accountSettings.html',
        controller  : 'accountSettingsController',
        methodName: 'email',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/account/password', {
        templateUrl : 'assets/templates/accountSettings.html',
        controller  : 'accountSettingsController',
        methodName: 'password',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/account/invoices', {
        templateUrl : 'assets/templates/accountSettings.html',
        controller  : 'accountSettingsController',
        methodName: 'invoices',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/classschedule', {
        templateUrl : 'assets/templates/classschedule.html',
        controller  : 'classScheduleController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/attendance', {
        templateUrl : 'assets/templates/attendance.html',
        controller  : 'attendanceController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/gradeLevels', {
        templateUrl : 'assets/templates/gradeLevels.html',
        controller  : 'gradeLevelsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/examsList', {
        templateUrl : 'assets/templates/examsList.html',
        controller  : 'examsListController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/events', {
        templateUrl : 'assets/templates/events.html',
        controller  : 'eventsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/events/:eventId', {
        templateUrl : 'assets/templates/events.html',
        controller  : 'eventsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/assignments', {
        templateUrl : 'assets/templates/assignments.html',
        controller  : 'assignmentsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/materials', {
        templateUrl : 'assets/templates/materials.html',
        controller  : 'materialsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/mailsms', {
        templateUrl : 'assets/templates/mailsms.html',
        controller  : 'mailsmsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/messages', {
        templateUrl : 'assets/templates/messages.html',
        controller  : 'messagesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/messages/:messageId', {
        templateUrl : 'assets/templates/messages.html',
        controller  : 'messagesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/onlineExams', {
        templateUrl : 'assets/templates/onlineExams.html',
        controller  : 'onlineExamsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/calender', {
        templateUrl : 'assets/templates/calender.html',
        controller  : 'calenderController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/transports', {
        templateUrl : 'assets/templates/transportation.html',
        controller  : 'TransportsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/settings', {
        templateUrl : 'assets/templates/settings.html',
        controller  : 'settingsController',
        methodName: 'settings',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/terms', {
        templateUrl : 'assets/templates/settings.html',
        controller  : 'settingsController',
        methodName: 'terms',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/media', {
        templateUrl : 'assets/templates/media.html',
        controller  : 'mediaController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/static', {
        templateUrl : 'assets/templates/static.html',
        controller  : 'staticController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/static/:pageId', {
        templateUrl: 'assets/templates/static.html',
        controller: 'staticController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/attendanceStats', {
        templateUrl : 'assets/templates/attendanceStats.html',
        controller  : 'attendanceStatsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/polls', {
        templateUrl : 'assets/templates/polls.html',
        controller  : 'pollsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/mailsmsTemplates', {
        templateUrl : 'assets/templates/mailsmsTemplates.html',
        controller  : 'mailsmsTemplatesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/feeType', {
        templateUrl : 'assets/templates/feeType.html',
        controller  : 'feeTypeController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/feeGroup', {
        templateUrl : 'assets/templates/feeGroup.html',
        controller  : 'feeGroupController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/feeAllocation', {
        templateUrl : 'assets/templates/feeAllocation.html',
        controller  : 'feeAllocationController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/invoices', {
        templateUrl : 'assets/templates/invoices.html',
        controller  : 'invoicesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/invoices/due', {
        templateUrl : 'assets/templates/invoices.html',
        controller  : 'invoicesController',
        methodName: 'dueinvoices',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/expenses', {
        templateUrl : 'assets/templates/expenses.html',
        controller  : 'expensesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/expensesCat', {
        templateUrl : 'assets/templates/expensesCat.html',
        controller  : 'expensesCatController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/languages', {
        templateUrl : 'assets/templates/languages.html',
        controller  : 'languagesController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/upgrade', {
        templateUrl : 'assets/templates/upgrade.html',
        controller  : 'upgradeController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/promotion', {
        templateUrl : 'assets/templates/promotion.html',
        controller  : 'promotionController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/academicYear', {
        templateUrl : 'assets/templates/academicYear.html',
        controller  : 'academicYearController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/staffAttendance', {
        templateUrl : 'assets/templates/staffAttendance.html',
        controller  : 'staffAttendanceController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/reports', {
        templateUrl : 'assets/templates/reports.html',
        controller  : 'reportsController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/vacation', {
        templateUrl : 'assets/templates/vacation.html',
        controller  : 'vacationController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .when('/mobileNotif', {
        templateUrl : 'assets/templates/mobileNotif.html',
        controller  : 'mobileNotifController',
        resolve: {
            essentialData: function(srvLibrary) {
                return srvLibrary.getEssentials();
            }
        }
    })

    .otherwise({
        redirectTo:'/'
    });

});

schoex.factory('srvLibrary', ['$http','$rootScope', function($http,$rootScope) {
    var sdo = {
        getEssentials: function() {
            if(typeof($rootScope.dashboardData) == "undefined"){
                var promise = $http({
                    method: 'GET',
                    url: 'index.php/dashboard'
                });
                promise.success(function(data, status, headers, conf) {
                    $rootScope.dashboardData = data;
                    $rootScope.phrase = $rootScope.dashboardData.language;

                    $rootScope.angDateFormat = $rootScope.dashboardData.dateformat;
                    if($rootScope.angDateFormat == ""){
                        $rootScope.angDateFormat = "MM/dd/yyyy";
                    }else{
                        $rootScope.angDateFormat = $rootScope.angDateFormat.replace('d','dd');
                        $rootScope.angDateFormat = $rootScope.angDateFormat.replace('m','MM');
                        $rootScope.angDateFormat = $rootScope.angDateFormat.replace('Y','yyyy');
                    }

                    if($rootScope.dashboardData.gcalendar == "ethiopic"){
                        $rootScope.dashboardData.gcalendar = "ethiopian";
                    }

                    return true;
                });
                return promise;
            }else{
                return true;
            }
        }
    }
    return sdo;
}]);

schoex.factory('dataFactory', function($http) {
    var myService = {
        httpRequest: function(url,method,params,dataPost,upload) {
            var passParameters = {};
            passParameters.url = url;

            if (typeof method == 'undefined'){
                passParameters.method = 'GET';
            }else{
                passParameters.method = method;
            }

            if (typeof params != 'undefined'){
                passParameters.params = params;
            }

            if (typeof dataPost != 'undefined'){
                passParameters.data = dataPost;
            }

            if (typeof upload != 'undefined'){
                passParameters.upload = upload;
            }

            var promise = $http(passParameters).then(function (response) {
                if(typeof response.data == 'string' && response.data != 1){
                    if(response.data.substr('loginMark')){
                        location.reload();
                        return;
                    }
                    $.toast({
                        heading: 'Schoex Error',
                        text: response.data,
                        position: 'top-right',
                        loaderBg:'#ff6849',
                        icon: 'error',
                        hideAfter: 3000,
                        stack: 6
                    });
                    return false;
                }
                if(response.data.jsMessage){
                    $.toast({
                        heading: response.data.jsTitle,
                        text: response.data.jsMessage,
                        position: 'top-right',
                        loaderBg:'#ff6849',
                        icon: 'info',
                        hideAfter: 3000,
                        stack: 6
                    });
                }
                return response.data;
            },function(response){
                if(response.data.substr('loginMark')){
                    location.reload();
                    return;
                }
                $.toast({
                    heading: 'Schoex Error',
                    text: 'An error occured while processing your request.',
                    position: 'top-right',
                    loaderBg:'#ff6849',
                    icon: 'error',
                    hideAfter: 3000,
                    stack: 6
                });
            });
            return promise;
        }
    };
    return myService;
});

schoex.directive('datePicker', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                var dateformatVal = jQuery('#dateformatVal').val();
                if(typeof dateformatVal == "undefined"){
                    var dateformatVal = $rootScope.dashboardData.dateformat;
                }
                var dateformat = dateformatVal;
                if(dateformat == ""){
                    dateformat = 'dd-mm-yyyy';
                }else{
                    dateformat = dateformat.replace('d','dd');
                    dateformat = dateformat.replace('m','mm');
                    dateformat = dateformat.replace('Y','yyyy');
                }

                var calendar = jQuery('#gcalendarVal').val();
                if(typeof calendar == "undefined"){
                    calendar = $rootScope.dashboardData.gcalendar;
                }
                calendar = $.calendars.instance(calendar);

                if(typeof attrs.id == "undefined"){
                    $(".datemask").calendarsPicker({calendar: calendar,dateFormat:dateformat,showAnim:''});
                }else{
                    $("#"+attrs.id).calendarsPicker({calendar: calendar,dateFormat:dateformat,showAnim:''});
                }
            };
        }
    };
});

schoex.directive('carouselInit', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('.carousel').carousel()
            };
        }
    };
});

schoex.directive('mobileNumber', function($parse, $timeout){
    return {
        restrict: 'A',
        require: 'ngModel',
        link: function(scope, element, attrs,ngModel) {
            var telInput = $(element);

            telInput.intlTelInput({utilsScript: jQuery('#utilsScript').val(),nationalMode: false});

            scope.$watch(attrs.ngModel, function(value) {
                telInput.intlTelInput("setNumber",element.val());
            });

            scope.$watch(attrs.ngModel, function(value) {
                if(value == "" || typeof value === "undefined"){
                    ngModel.$setValidity(attrs.ngModel, true);
                    return;
                }
                if (telInput.intlTelInput("isValidNumber")) {
                    ngModel.$setValidity(attrs.ngModel, true);
                } else {
                    ngModel.$setValidity(attrs.ngModel, false);
                }
            });
        }
    };
});
schoex.directive('checklistModel', ['$parse', '$compile', function($parse, $compile) {
    // contains
    function contains(arr, item, comparator) {
        if (angular.isArray(arr)) {
            for (var i = arr.length; i--;) {
                if (comparator(arr[i], item)) {
                    return true;
                }
            }
        }
        return false;
    }

    // add
    function add(arr, item, comparator) {
        arr = angular.isArray(arr) ? arr : [];
        if(!contains(arr, item, comparator)) {
            arr.push(item);
        }
        return arr;
    }

    // remove
    function remove(arr, item, comparator) {
        if (angular.isArray(arr)) {
            for (var i = arr.length; i--;) {
                if (comparator(arr[i], item)) {
                    arr.splice(i, 1);
                    break;
                }
            }
        }
        return arr;
    }

    // http://stackoverflow.com/a/19228302/1458162
    function postLinkFn(scope, elem, attrs) {
        // exclude recursion, but still keep the model
        var checklistModel = attrs.checklistModel;
        attrs.$set("checklistModel", null);
        // compile with `ng-model` pointing to `checked`
        $compile(elem)(scope);
        attrs.$set("checklistModel", checklistModel);

        // getter for original model
        var checklistModelGetter = $parse(checklistModel);
        var checklistChange = $parse(attrs.checklistChange);
        var checklistBeforeChange = $parse(attrs.checklistBeforeChange);
        var ngModelGetter = $parse(attrs.ngModel);



        var comparator = angular.equals;

        if (attrs.hasOwnProperty('checklistComparator')){
            if (attrs.checklistComparator[0] == '.') {
                var comparatorExpression = attrs.checklistComparator.substring(1);
                comparator = function (a, b) {
                    return a[comparatorExpression] === b[comparatorExpression];
                };

            } else {
                comparator = $parse(attrs.checklistComparator)(scope.$parent);
            }
        }

        // watch UI checked change
        var unbindModel = scope.$watch(attrs.ngModel, function(newValue, oldValue) {
            if (newValue === oldValue) {
                return;
            }

            if (checklistBeforeChange && (checklistBeforeChange(scope) === false)) {
                ngModelGetter.assign(scope, contains(checklistModelGetter(scope.$parent), getChecklistValue(), comparator));
                return;
            }

            setValueInChecklistModel(getChecklistValue(), newValue);

            if (checklistChange) {
                checklistChange(scope);
            }
        });

        // watches for value change of checklistValue
        var unbindCheckListValue = scope.$watch(getChecklistValue, function(newValue, oldValue) {
            if( newValue != oldValue && angular.isDefined(oldValue) && scope[attrs.ngModel] === true ) {
                var current = checklistModelGetter(scope.$parent);
                checklistModelGetter.assign(scope.$parent, remove(current, oldValue, comparator));
                checklistModelGetter.assign(scope.$parent, add(current, newValue, comparator));
            }
        }, true);

        var unbindDestroy = scope.$on('$destroy', destroy);

        function destroy() {
            unbindModel();
            unbindCheckListValue();
            unbindDestroy();
        }

        function getChecklistValue() {
            return attrs.checklistValue ? $parse(attrs.checklistValue)(scope.$parent) : attrs.value;
        }

        function setValueInChecklistModel(value, checked) {
            var current = checklistModelGetter(scope.$parent);
            if (angular.isFunction(checklistModelGetter.assign)) {
                if (checked === true) {
                    checklistModelGetter.assign(scope.$parent, add(current, value, comparator));
                } else {
                    checklistModelGetter.assign(scope.$parent, remove(current, value, comparator));
                }
            }

        }

        // declare one function to be used for both $watch functions
        function setChecked(newArr, oldArr) {
            if (checklistBeforeChange && (checklistBeforeChange(scope) === false)) {
                setValueInChecklistModel(getChecklistValue(), ngModelGetter(scope));
                return;
            }
            ngModelGetter.assign(scope, contains(newArr, getChecklistValue(), comparator));
        }

        // watch original model change
        // use the faster $watchCollection method if it's available
        if (angular.isFunction(scope.$parent.$watchCollection)) {
            scope.$parent.$watchCollection(checklistModel, setChecked);
        } else {
            scope.$parent.$watch(checklistModel, setChecked, true);
        }
    }

    return {
        restrict: 'A',
        priority: 1000,
        terminal: true,
        scope: true,
        compile: function(tElement, tAttrs) {

            if (!tAttrs.checklistValue && !tAttrs.value) {
                throw 'You should provide `value` or `checklist-value`.';
            }

            // by default ngModel is 'checked', so we set it if not specified
            if (!tAttrs.ngModel) {
                // local scope var storing individual checkbox model
                tAttrs.$set("ngModel", "checked");
            }

            return postLinkFn;
        }
    };
}]);
schoex.directive('ngEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if(event.which === 13) {
                scope.$apply(function (){
                    scope.$eval(attrs.ngEnter);
                });

                event.preventDefault();
            }
        });
    };
});
schoex.directive('chatBox', function($parse, $timeout){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#chat-box').slimScroll({
                    height: '500px',alwaysVisible: true,start : "bottom"
                });
            };
        }
    };
});
schoex.directive('scrollBox', function($parse, $timeout){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#'+attrs.id).slimScroll({
                    height: attrs.height,alwaysVisible: true,start : "bottom"
                });
            };
        }
    };
});
schoex.directive('invoceDougnuts', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                var doughnutChart = echarts.init(document.getElementById('m-piechart'));
                // specify chart configuration item and data
                option = {
                    tooltip: {
                        trigger: 'item'
                        , formatter: "{a} <br/>{b} : {c} ({d}%)"
                    }
                    , legend: {
                        orient: 'horizontal'
                        , x: 'center'
                        , show: false
                        , y: 'bottom'
                        , data: ['80', '60', '20', '140']
                    }
                    , toolbox: {
                        show: false
                        , feature: {
                            dataView: {
                                show: true
                                , readOnly: false
                            }
                            , magicType: {
                                show: false
                                , type: ['pie', 'funnel']
                                , option: {
                                    funnel: {
                                        x: '25%'
                                        , width: '50%'
                                        , funnelAlign: 'center'
                                        , max: 1548
                                    }
                                }
                            }
                            , restore: {
                                show: true
                            }
                            , saveAsImage: {
                                show: true
                            }
                        }
                    }
                    , color: ["#745af2", "#f62d51"]
                    , calculable: true
                    , series: [
                        {
                            name: 'Invoices'
                            , type: 'pie'
                            , radius: ['70%', '90%']
                            , itemStyle: {
                                normal: {
                                    label: {
                                        show: false
                                    }
                                    , labelLine: {
                                        show: false
                                    }
                                }
                                , emphasis: {
                                    label: {
                                        show: true
                                        , position: 'center'
                                        , textStyle: {
                                            fontSize: '30'
                                            , fontWeight: 'bold'
                                        }
                                    }
                                }
                            }
                            , data: [
                                {
                                    value: $rootScope.dashboardData.stats.invoices, name: 'Invoices'
                                }
                                , {
                                    value: $rootScope.dashboardData.stats.dueInvoices, name: 'Due Invoices'
                                }
                                ]
                            }
                        ]
                };
                // use configuration item and data specified to show chart
                doughnutChart.setOption(option, true), $(function () {
                    function resize() {
                        setTimeout(function () {
                            doughnutChart.resize()
                        }, 100)
                    }
                    $(window).on("resize", resize), $(".sidebartoggler").on("click", resize)
                });
            };
        }
    };
});
schoex.directive('colorbox', function() {
    return {
        restrict: 'AC',
        link: function (scope, element, attrs) {
            var itemsVars = {transition:'elastic',title:attrs.title,rel:'gallery',scalePhotos:true};
            if(attrs.youtube){
                itemsVars['iframe'] = true;
                itemsVars['innerWidth'] = 640;
                itemsVars['innerHeight'] = 390;
            }
            if(attrs.vimeo){
                itemsVars['iframe'] = true;
                itemsVars['innerWidth'] = 500;
                itemsVars['innerHeight'] = 409;
            }
            if(!attrs.youtube && !attrs.vimeo){
                itemsVars['height'] = "100%";
            }
            $(element).colorbox(itemsVars);
        }
    };
});
schoex.directive('ckEditor', [function () {
    return {
        require: '?ngModel',
        link: function ($scope, elm, attr, ngModel) {
            var ck = CKEDITOR.replace(elm[0]);

            ck.on('pasteState', function () {
                $scope.$apply(function () {
                    ngModel.$setViewValue(ck.getData());
                });
            });

            ngModel.$render = function (value) {
                ck.setData(ngModel.$modelValue);
            };
        }
    };
}]);
schoex.directive('calendarBox', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                var calendar = $.calendars.instance($rootScope.dashboardData.gcalendar);
                $('#calendar').calendarsPicker({calendar: calendar,showOtherMonths:false,selectOtherMonths:false,onSelect:null,onChangeMonthYear:showOtherCalEvents});

                var todayDate = calendar.today();
                var d = calendar.newDate(todayDate._year, todayDate._month, 1);

                var start = calendar.minDay+"-"+todayDate._month+"-"+todayDate._year;
                var end = d.daysInMonth()+"-"+todayDate._month+"-"+todayDate._year;

                $.get("index.php/calender",{start : start, end : end},function(data) {
                        populateEventsInFullCal(data,$rootScope.dashboardData.gcalendar);
                    }
                );
            };
        }
    };
});

function showOtherCalEvents(year,month,inst) {
    var gc = $.calendars.instance(inst.drawDate._calendar.local['name']);
    var d = gc.newDate(year, month, 1);

    var start = gc.minDay+"-"+month+"-"+year;
    var end = d.daysInMonth()+"-"+month+"-"+year;

    $.get("index.php/calender",{start : start, end : end},function(data) {
            populateEventsInFullCal(data,inst.drawDate._calendar.local['name']);
        }
    );
}

function populateEventsInFullCal(events,cal_name){
    $.each( events, function( key, value ) {
        if($("#"+value.id).length == 0){
            $(".jdd"+value.start).after( "<a href='" + value.url + "' class='fullCalEvent' style='color:" + value.textColor + " !important;background-color:" + value.backgroundColor + " !important' id='" + value.id + "'>" + value.title + "</a>" );
        }
    });
}

schoex.directive('smsCounter', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#messageContentSms').countSms('#sms-counter');
            };
        }
    };
});
schoex.directive('modal', function () {
    return {
        template: '<div class="modal fade">' +
        '<div class="modal-dialog {{modalClass}}">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>' +
        '<h4 class="modal-title">{{ modalTitle }}</h4>' +
        '</div>' +
        '<div class="modal-body" ng-transclude></div>' +
        '</div>' +
        '</div>' +
        '</div>',
        restrict: 'E',
        transclude: true,
        replace:true,
        scope:true,
        link: function postLink(scope, element, attrs) {
            scope.$watch(attrs.visible, function(value){
                if(value == true)
                $(element).modal('show');
                else
                $(element).modal('hide');
            });

            $(element).on('shown.bs.modal', function(){
                scope.$apply(function(){
                    scope.$parent[attrs.visible] = true;
                });
            });

            $(element).on('hidden.bs.modal', function(){
                scope.$apply(function(){
                    scope.$parent[attrs.visible] = false;
                });
            });
        }
    };
});

schoex.directive('scalendarBox', function($parse, $timeout,$rootScope){
    return {
        restrict: 'A',
        replace: true,
        transclude: false,
        compile: function(element, attrs) {
            return function (scope, slider, attrs, controller) {
                $('#scalendar').fullCalendar({
                    events: "calender",
                    lang: $rootScope.dashboardData.languageUniversal
                });
            };
        }
    };
});
schoex.directive('tooltip', function(){
    return {
        restrict: 'A',
        link: function(scope, element, attrs){
            $(element).hover(function(){
                $(element).tooltip('show');
            }, function(){
                $(element).tooltip('hide');
            });
        }
    };
});
schoex.directive('showtab',
function () {
    return {
        link: function (scope, element, attrs) {
            element.click(function(e) {
                e.preventDefault();
                $(element).tab('show');
            });
        }
    };
});


schoex.filter('object2Array', function() {
    return function(input) {
        var out = [];
        for(i in input){
            out.push(input[i]);
        }
        return out;
    }
});

function uploadSuccessOrError(response){
    if(typeof response == 'string' && response != 1){
        $.toast({
            heading: 'School Application',
            text: response,
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'error',
            hideAfter: 3000,
            stack: 6
        });
        return false;
    }
    if(response.jsMessage){
        $.toast({
            heading: response.jsTitle,
            text: response.jsMessage,
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'info',
            hideAfter: 3000,
            stack: 6
        });
    }
    if(response.jsStatus){
        if(response.jsStatus == "0"){
            return false;
        }
    }
    return response;
}

function successOrError(data){
    if(data.jsStatus){
        if(data.jsStatus == "0"){
            return false;
        }
    }
    return data;
}

//New Functions Implementation

function apiResponse(response,image){
    if(response.status){
        if(typeof response.title !== 'undefined'){
            if(response.status == "success"){
                $.toast({
                    heading: response.title,
                    text: response.message,
                    position: 'top-right',
                    loaderBg:'#ff6849',
                    icon: 'success',
                    hideAfter: 3000,
                    stack: 6
                });
            }
            if(response.status == "failed"){
                $.toast({
                    heading: response.title,
                    text: response.message,
                    position: 'top-right',
                    loaderBg:'#ff6849',
                    icon: 'error',
                    hideAfter: 3000,
                    stack: 6
                });
            }
        }
        if(response.data){
            return response.data;
        }
    }else{
        return response;
    }
}

function apiModifyTable(originalData,id,response){
    angular.forEach(originalData, function (item,key) {
        if(item.id == id){
            originalData[key] = response;
        }
    });
    return originalData;
}
