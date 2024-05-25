

jQuery(document).ready(function($) {
    var debounceTimeout = null;

    // Initialize Select2 with custom settings
    $('#reassign_user').select2({
        minimumInputLength: 3, // Set minimum input length to 3 to avoid fetching users without a search term
        ajax: {
            url: ajaxurl, // WordPress AJAX URL
            dataType: 'json',
            delay: 300, // Debounce delay for better performance
            data: function(params) {
                return {
                    action: 'custom_fetch_users',
                    q: params.term, // Use the current search term
                    page: params.page || 1 // Add pagination
                };
            },
            processResults: function(data, params) {
                params.page = params.page || 1;
                return {
                    results: data,
                    pagination: {
                        more: data.length === 10 // Assuming 10 results per page
                    }
                };
            },
            cache: true,
            transport: function(params, success, failure) {
                // Clear the previous timeout
                clearTimeout(debounceTimeout);

                // Set a new timeout
                debounceTimeout = setTimeout(function() {
                    var $request = $.ajax(params);
                    $request.then(success);
                    $request.fail(failure);
                    return $request;
                }, 300); // Adjust the debounce delay as needed (300ms in this case)
            }
        },
        placeholder: 'Start typing to search for users',
        allowClear: true,
        dropdownCssClass: 'custom-select2-dropdown', // Custom class for styling
        language: {
            loadingMore: function() {
                return 'Loading more results...';
            }
        }
    }).on('change', function(e) {
        // Your custom onChange function logic here
        var selectedUser = $(this).val();
        console.log("Selected user ID: " + selectedUser);
        // Example: Update a hidden field or perform an AJAX request based on selectedUser
        // $('#hidden_field').val(selectedUser);
    });

    // Disable default Select2 search on input
    $('#reassign_user').on('input', function() {
        // Prevent the default search behavior on input
        return false;
    });

    // Clear search term on select2 close
    $('#reassign_user').on('select2:close', function() {
        searchTerm = ''; // Reset search term when dropdown closes
    });
});
