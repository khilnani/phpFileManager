$(function() {
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/eclipse");
    editor.getSession().setMode("ace/mode/html");

    var mode = getModeForPath( $('#fileName').text() );
    console.log("Setting mode: " + mode.mode);
    editor.getSession().setMode(mode.mode);

    editor.getSession().on('change', function(e) {
        console.log("saveText");
        $("#newcontent").val(editor.getValue());
    });

    editor.setValue($("#newcontent").val());
    console.log("Default text set.");
    editor.clearSelection();

    /*
    editor.commands.addCommand({
        name: 'Save',
        bindKey: {
            win: 'Ctrl-S',
            mac: 'Command-S'
        },
        exec: function(editor) {
            console.log("saveForm");
            $("#newcontent").val(editor.getValue());
            
            var c = confirm("Save changes?");
            if (c) $('#mainForm').submit();
        },
        readOnly: true // false if this command should not apply in readOnly mode
    });
    */

    $('#save').click( function() {
        var c = confirm("Save changes?");
        if (c) return true;
        return false;
    });

    $('#cancel').click( function() {
        var c = confirm("Discard changes?");
        if (c) return true;
        return false;
    });
});