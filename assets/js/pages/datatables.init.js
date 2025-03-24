$(document).ready(function() {
    var tableButtons;

    // Initialize the main DataTable
    $('#datatable').DataTable();

    // Initialize the DataTable with buttons
    if (!$.fn.dataTable.isDataTable('#datatable-buttons')) {
        console.log("Initializing DataTable for the first time...");

        tableButtons = $('#datatable-buttons').DataTable({
            lengthChange: false,
            dom: 'Bfrtip',
            buttons: [
                'copy',
                'excel',
                'pdf',
                'colvis',
            ],
            order: [
                [5, 'desc']
            ],
            // Store the initial column visibility state
            columnDefs: [{
                targets: '_all',
                visible: true
            }]
        });

        // Append buttons container
        tableButtons.buttons().container().appendTo('#datatable-buttons_wrapper .col-md-6:eq(0)');
    }

    // Handle the form submission for filtering
    $(document).on('submit', 'form', function(e) {
        var form = $(this);

        e.preventDefault(); // Prevent default form submission

        console.log("Form submitted for filtering...");

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(response) {
                console.log("Raw Response from server: ", response); // Inspect raw data

                // Parse the response into a table format (if HTML is expected)
                var rows = $(response); // Wrap the response in a jQuery object

                // Clear old data and add new rows
                tableButtons.clear();
                tableButtons.rows.add(rows); // Add new rows
                tableButtons.draw(); // Redraw the table to reflect changes

                // Add totals to a footer or display as needed
                var totalReglement = rows.last().find('td:nth-child(8)').text();
                var totalDebit = rows.last().find('td:nth-child(9)').text();
                var totalSolde = rows.last().find('td:nth-child(10)').text();
                console.log("Totals:", totalReglement, totalDebit, totalSolde);
            },
            error: function(xhr, status, error) {
                console.error('Error: ' + error);
                alert('An error occurred while fetching data: ' + error);
            }
        });
    });

    // Add event listener for the column visibility change
    tableButtons.on('column-visibility.dt', function(e, settings, column, state) {
        // Ensure the export is updated with the visibility state
        tableButtons.buttons('excel').action(function(e, dt, button, config) {
            // Ensure the hidden columns aren't included in the export
            var visibleColumns = tableButtons.columns(':visible').indexes().toArray();
            config.columns = visibleColumns; // Only export visible columns
        });
    });

    // Style the DataTables length dropdown
    $(".dataTables_length select").addClass('form-select form-select-sm');
});