
/**
 * Client Dashboard JS
 */

(function (window, undefined) {
  'use strict';

  /**
   * Module
   */
  angular.module('wpiClientDashboard', ['ui.bootstrap','ngSanitize']).

  /**
   * Controller
   */
  controller('InvoiceList', function ( $scope, $http ) {

    /**
     * Flags
     * @type {boolean}
     */
	$scope.invoice_date_title ='';
	$scope.allowed_status='other';
    $scope.isLoading = true;
    $scope.isError = false;

    /**
     * List of invoices to display
     * @type {Array}
     */
    $scope.displayInvoices = [];

    /**
     * Pagination
     * @type {number}
     */
    $scope.currentPage = 1;
    $scope.perPage = '10';
    $scope.totalItems = 0;
    $scope.maxSize = 5;
    $scope.user = null;

    /**
     * Invoices Amount
     * @type {number}
     */
    $scope.totalAmount = 0;

    /**
     * Init function
     */
    $scope.init = function( user ) {

      if ( typeof user == 'object' ) {
        $scope.user = user;
      }
      /**
       * Load first page of invoices
       */
      $scope.loadInvoices( 0, $scope.perPage );
    };
	
	/**
	 * Click Paid/Unpaid invoice buttons
	 * @param {type} type
	 * 
	 */
	  $scope.setInvoiceType = function( type ) {
		$scope.allowed_status = type;
		$scope.loadInvoices( 0, $scope.perPage );
		if( type == 'other' ) { 
			$( "#btn-paid" ).removeClass( 'active' );
			$( "#btn-other" ).addClass( 'active' );
		} else {
			$( "#btn-paid" ).addClass( 'active' );
			$( "#btn-other" ).removeClass( 'active' );
		}
	};

    /**
     * Click invoice handler
     * @param permalink
     */
    $scope.goToInvoice = function( permalink ) {
      window.location = permalink;
    };

    /**
     * Pagination change handler
     */
    $scope.paginate = function() {
      $scope.loadInvoices( ($scope.currentPage-1)*$scope.perPage, $scope.perPage );
    };

    /**
     * Invoices loader
     * @param offset
     * @param per_page
     */
    $scope.loadInvoices = function( offset, per_page ) {

      $scope.isLoading = true;

      $http( {
        method: 'GET',
        url: ajaxurl,
        params: {
          action: 'cd_get_invoices',
          offset: offset,
          per_page: per_page,
          wpi_user_id: $scope.user.wpi_user_id || false,
          wpi_token: $scope.user.wpi_token || false,
		  allowed_status: $scope.allowed_status
        }
      } ).success(function(data) {

        try {
          if ( typeof data == 'string' ) {
            JSON.parse(data);
          }
        } catch (e){
          $scope.isError = true;
          $scope.isLoading = false;
          return;
        }

        $scope.displayInvoices = data.items;
        $scope.totalItems = data.total;
        $scope.totalAmount = data.amount;
		$scope.invoice_date_title = data.invoice_date_title;
		$scope.isLoading = false;
      });
    };

  });

})(this);
