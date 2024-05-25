jQuery(document).ready(function($) {
    function fetchNotes() {
        $.post(wpStickyNotes.ajax_url, { action: 'get_notes' }, function(response) {
            if (response.success) {
                $('#notes-container').empty();
                response.data.forEach(function(note) {
                    $('#notes-container').append(
                        '<div class="note" data-id="' + note.id + '" style="background-color: ' + note.color + '">' +
                        '<h3 class="note-heading">' + escapeHtml(note.heading) + '</h3>' +
                        '<pre class="note-description">' + escapeHtml(note.description) + '</pre>' +
                        '<button class="edit-note">Edit</button>' +
                        '<button class="delete-note">Delete</button>' +
                        '<button class="share-note">Share</button></div>'
                    );
                });
            }
        });
    }

    $('#add-note').click(function() {
        var heading = $('#new-note-heading').val();
        var description = $('#new-note-description').val();
        var color = $('#new-note-color').val();
        $.post(wpStickyNotes.ajax_url, { action: 'add_note', heading: heading, description: description, color: color }, function(response) {
            if (response.success) {
                fetchNotes();
                $('#new-note-heading').val('');
                $('#new-note-description').val('');
                $('#new-note-color').wpColorPicker('color', '#000000');
            }
        });
    });

    $('#notes-container').on('click', '.delete-note', function() {
        var noteId = $(this).parent().data('id');
        $.post(wpStickyNotes.ajax_url, { action: 'delete_note', id: noteId }, function(response) {
            if (response.success) {
                fetchNotes();
            }
        });
    });

    $('#notes-container').on('click', '.edit-note', function() {
        var note = $(this).parent();
        var id = note.data('id');
        var heading = note.find('.note-heading').text();
        var description = note.find('.note-description').text();
        var color = note.css('background-color');

        $('#edit-note-id').val(id);
        $('#edit-note-heading').val(heading);
        $('#edit-note-description').val(description);
        $('#edit-note-color').wpColorPicker('color', rgbToHex(color));

        $('#edit-note-modal').show();
    });

    $('#save-note').click(function() {
        var id = $('#edit-note-id').val();
        var heading = $('#edit-note-heading').val();
        var description = $('#edit-note-description').val();
        var color = $('#edit-note-color').val();
        $.post(wpStickyNotes.ajax_url, { action: 'edit_note', id: id, heading: heading, description: description, color: color }, function(response) {
            if (response.success) {
                fetchNotes();
                $('#edit-note-modal').hide();
            }
        });
    });

    $('#notes-container').on('click', '.share-note', function() {
        var noteId = $(this).parent().data('id');
        var noteHeading = $(this).parent().find('.note-heading').text();
        var shareUrl = window.location.origin + '/?note_id=' + noteId + '&note_heading=' + encodeURIComponent(noteHeading.toLowerCase().replace(/ /g, '-'));
        prompt('Share this URL to view the note:', shareUrl);
    });

    $('.color-picker').wpColorPicker();

    // Close modal
    $('.close').click(function() {
        $('#edit-note-modal').hide();
    });

    fetchNotes();

    function rgbToHex(rgb) {
        var result = /^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*\d*\.?\d+)?\)$/.exec(rgb);
        return result ? "#" + ((1 << 24) + (parseInt(result[1]) << 16) + (parseInt(result[2]) << 8) + parseInt(result[3])).toString(16).slice(1) : rgb;
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
