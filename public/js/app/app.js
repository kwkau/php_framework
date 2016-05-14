var hbcmis = angular.module("hbcmis",["ionic",'720kb.datepicker'])
    .config(function ($stateProvider, $urlRouterProvider) {
        $stateProvider
           /*-----------------------
            * app index state login
            *---------------------*/
            .state("login",{
                url : "/",
                templateUrl : "public/js/views/login-view.html",
                controller : "LoginController",
                controllerAs : "lgn"
            })
           /*------------------------
            * parent state dashboard
            *----------------------*/

            .state("dashboard",{
                url:"/dashboard",
                templateUrl : "public/js/views/dashboard-view.html",
                controller:"DashboardController"
            })

            .state("dashboard.landing",{
                url:"/landing",
                views:{
                    menuContent:{
                        templateUrl : "public/js/views/landing.html"
                    }
                }
            })
               /*-------------------------------
                * parent dashboard child states
                *-----------------------------*/
                .state("dashboard.add-member",{
                    url:"/addMember",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/add-member-view.html",
                            controller:"AddMemberController"
                        }
                    }
                })
                .state("dashboard.add-activity",{
                    url:"/addActivity",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/activity/add-activity-view.html",
                            controller:"AddActivityController"
                        }
                    }
                })
                .state("dashboard.attendance",{
                    url:"/attendance",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/attendance-view.html",
                            controller: "AttendanceController"
                        }
                    }

                })
                .state("dashboard.offering",{
                    url:"/offering",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/offering-view.html",
                            controller: "OfferingController"
                        }
                    }
                })
                .state("dashboard.view-member",{
                    url:"/viewMember",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/member-view.html",
                            controller:"ViewMemberController"
                        }
                    }
                })
                .state("dashboard.branch-add",{
                    url:"/add",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/add-branch-view.html",
                            controller: "AddBranchController"
                        }
                    }
                })
                .state("dashboard.branch-leaders",{
                    url:"/branchLeaders",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/branch-leaders-view.html",
                            controller:"BranchLeadersController"
                        }
                    }
                })
                .state("dashboard.branch-view",{
                    url:"/viewBranch",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/edit-branch-view.html",
                            controller:"EditBranchController"
                        }
                    }
                })
                .state("dashboard.view-attendance",{
                    url:"/viewAttendance",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/view-attendance-view.html",
                            controller:"ViewAttendanceController"
                        }
                    }
                })
                .state("dashboard.view-offering",{
                    url:"/viewOffering",
                    views:{
                        menuContent:{
                            templateUrl : "public/js/views/view-offering-view.html",
                            controller:"ViewOfferingController"
                        }
                    }
                })
            .state("dashboard.ministry-add",{
                url:"/addMinistry",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/add-ministry-view.html",
                        controller:"AddMinistryController"
                    }
                }
            })
            .state("dashboard.ministry-view",{
                url:"/viewMinistry",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/view-ministry-view.html",
                        controller:"ViewMinistryController"
                    }
                }
            })
            .state("dashboard.add-currency",{
                url:"/addCurrency",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/currency/add-currency-view.html",
                        controller:"AddCurrencyController"
                    }
                }
            })
            .state("dashboard.migration-view",{
                url:"/memberMigration",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/migration.html",
                        controller:"MigrationController"
                    }
                }
            })
            .state("dashboard.inventory",{
                url:"/inventory",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/inventory/inventory-view.html",
                        controller:"InventoryController"
                    }
                }
            })
            .state("dashboard.item-categories",{
                url:"/itemCategories",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/category/category-view.html",
                        controller:"ItemCategoryController"
                    }
                }
            })
            .state("dashboard.diocese",{
                url:"/diocese",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/diocese/diocese-view.html",
                        controller:"DioceseController"
                    }
                }
            })
            .state("dashboard.zones",{
                url:"/zones",
                views:{
                    menuContent:{
                        templateUrl:"public/js/views/zones/zone-view.html",
                        controller:"ZoneController"
                    }
                }
            });
        $urlRouterProvider.otherwise("/");//index url
    })
    .directive("fileUpload", function () {
        return{
            scope:true,
            link: function (scope,el,attrs) {
                el.bind("change", function (event) {
                   var files = event.target.files;
                    for(var i = 0; i < files.length; i++){
                        scope.$emit("fileSelected",{file: files[i]});
                    }
                });
            }
        }
    });