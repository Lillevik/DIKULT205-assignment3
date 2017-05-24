/**
 * This function reads an image file that the users
 * selects and then displays it in the preview area.
 * @param input
 */
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            var image = $('#preview');
            image.attr('src', e.target.result);
            image.css({'display': 'block'});
            $('#title').css({'margin':'10px auto'})
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * This function adds a limit for how many checkboxes can be
 * checked and alerts the user if the limit has been reached.
 * @param checkgroup
 * @param limit
 */
function checkboxlimit(checkgroup, limit){
    for (var i=0; i<checkgroup.length; i++){
        checkgroup[i].onclick=function(){
            var checkedcount=0
            for (var i=0; i<checkgroup.length; i++)
                checkedcount+=(checkgroup[i].checked)? 1 : 0
            if (checkedcount>limit){
                alert("You can only select a maximum of "+limit+" tags for your post.")
                this.checked=false
            }
        }
    }
}

/**
 * This function takes an inputfield and adds an event listener
 * that displays the current length of the content each time something
 * changes.
 *
 * @param field
 * @param displayElement
 * @param limit
 */
function display_current_input_length(field, displayElement, limit){
    field.addEventListener('input', function(){
        displayElement.innerHTML = parseInt(this.value.length);
    })
}

//The onload function that runs when the window loads
window.onload = function(){
    checkboxlimit(document.getElementById('post-form'), 5);

    $("#file-upload").change(function(){
        readURL(this);
        var filename = $('input[type=file]').val().replace(/C:\\fakepath\\/i, '');
        if(filename != ""){
            if(filename.substr('.jpeg' | '.jpg' | '.gif' | '.png')){
                {
                    $('.file_text').text(filename);
                }
            }else{
                this.style('display','none');
                $('.file_text').text("File is not an image.");
            }

        }
    });

    const title = document.getElementById('title');
    const titleChars = document.getElementById('number-of-title-chars')
    display_current_input_length(title, titleChars, 100);

    const description = document.getElementById('description');
    const descChars = document.getElementById('number-of-description-chars');
    display_current_input_length(description, descChars, 300)



};
