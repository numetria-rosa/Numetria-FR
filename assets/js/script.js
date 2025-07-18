'use strict';


app.config({

  /*
  |--------------------------------------------------------------------------
  | Autoload
  |--------------------------------------------------------------------------
  |
  | By default, the app will load all the required plugins from /assets/vendor/
  | directory. If you need to disable this functionality, simply change the
  | following variable to false. In that case, you need to take care of loading
  | the required CSS and JS files into your page.
  |
  */

  autoload: true,

  /*
  |--------------------------------------------------------------------------
  | Provide
  |--------------------------------------------------------------------------
  |
  | Specify an array of the name of vendors that should be load in all pages.
  | Visit following URL to see a list of available vendors.
  |
  | https://thetheme.io/theadmin/help/article-dependency-injection.html#provider-list
  |
  */

  provide: [],

  /*
  |--------------------------------------------------------------------------
  | Google API Key
  |--------------------------------------------------------------------------
  |
  | Here you may specify your Google API key if you need to use Google Maps
  | in your application
  |
  | Warning: You should replace the following value with your own Api Key.
  | Since this is our own API Key, we can't guarantee that this value always
  | works for you.
  |
  | https://developers.google.com/maps/documentation/javascript/get-api-key
  |
  */

  googleApiKey: '',

  /*
  |--------------------------------------------------------------------------
  | Google Analytics Tracking
  |--------------------------------------------------------------------------
  |
  | If you want to use Google Analytics, you can specify your Tracking ID in
  | this option. Your key would be a value like: UA-XXXXXXXX-Y
  |
  */

  googleAnalyticsId: '',

  /*
  |--------------------------------------------------------------------------
  | Smooth Scroll
  |--------------------------------------------------------------------------
  |
  | By changing the value of this option to true, the browser's scrollbar
  | moves smoothly on scroll.
  |
  */

  smoothScroll: true,

  /*
  |--------------------------------------------------------------------------
  | Save States
  |--------------------------------------------------------------------------
  |
  | If you turn on this option, we save the state of your application to load
  | them on the next visit (e.g. make topbar fixed).
  |
  | Supported states: Topbar fix, Sidebar fold
  |
  */

  saveState: false,

  /*
  |--------------------------------------------------------------------------
  | Cache Bust String
  |--------------------------------------------------------------------------
  |
  | Adds a cache-busting string to the end of a script URL. We automatically
  | add a question mark (?) before the string. Possible values are: '1.2.3',
  | 'v1.2.3', or '123456789'
  |
  */

  cacheBust: '',


});



// Codes to be execute when all JS files are loaded and ready to use
//
app.ready(function() {


  // Page: index.php
  // Earnings chart
  //
  if ( window['Chart'] != undefined ) {
    new Chart($("#chartjs-earnings"), {
      type: 'line',
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "Jun", "Jul"],
        datasets: [
          {
            label: "Invoices",
            backgroundColor: "rgba(51,202,185,0.5)",
            borderColor: "rgba(51,202,185,0.5)",
            pointRadius: 0,
            data: [0, 6000, 8000, 5500, 2000, 5000, 4000]
          },
          {
            label: "Payments",
            backgroundColor: "rgba(243,245,246,0.8)",
            borderColor: "rgba(243,245,246,0.8)",
            pointRadius: 0,
            data: [9000, 5000, 4000, 2000, 8000, 3000, 9000]
          }
        ]
      },
      options: {
        legend: {
          display: false
        },
      }
    });
  }



  // Page: invoices.html
  // Add a new item row in "create new invoice"
  //
  $(document).on('click', '#btn-new-item', function() {
    var html = '' +
        '<div class="form-group input-group flex-items-middle">' +
          '<select title="Item" data-provide="selectpicker" data-width="100%">' +
            '<option>Website design</option>' +
            '<option>PSD to HTML</option>' +
            '<option>Website re-design</option>' +
            '<option>UI Kit</option>' +
            '<option>Full Package</option>' +
          '</select>' +
          '<div class="input-group-input">' +
            '<input type="text" class="form-control">' +
            '<label>Quantity</label>' +
          '</div>' +
          '<a class="text-danger pl-12" id="btn-remove-item" href="#" title="Remove" data-provide="tooltip"><i class="ti-close"></i></a>' +
        '</div>';

    $(this).before(html);
  });


  // Page: invoices.html
  // Remove an item row in "create new invoice"
  //
  $(document).on('click', '#btn-remove-item', function() {
    $(this).closest('.form-group').fadeOut(function(){
      $(this).remove();
    });
  });



});
