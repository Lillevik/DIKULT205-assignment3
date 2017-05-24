/**
 * This file handles the post.php page, which means
 * all the comment appending to the parents.
 */


var currentShown = null;
const params = get_url_parameters();
var working = false;

function get_url_parameters() {
        result = {};

            var uri = document.location.href;

            if (uri.indexOf("?") < 0)
                return result;
        query_string = uri.substring(uri.indexOf("?"));
        if (query_string.indexOf("#") >= 0)
                query_string = query_string.substring(0, query_string.indexOf("#"));


                query_pattern = /(?:[?&])([^&=]+)(?:=)([^&]*)/g;
        decode = function(str) {return decodeURIComponent(str.replace(/\+/g, " "));};
        while(match = query_pattern.exec(query_string))
                result[decode(match[1])] = decode(match[2]);
        return result;
}


/**
 * This function displays a new textarea when the user
 * clicks on a reply button for a child comment.
 * @param id
 */
function show_input(id){
    if(currentShown != null){
        currentShown.remove();
    }
    var parent = document.getElementById(id);

    var otherLiElemens = parent.querySelectorAll('.text-area-list-element');
    for(var i = 0; i < otherLiElemens.length; i++){
        otherLiElemens[i].remove();
    }

    var listElem = document.createElement('li');
    listElem.classList.add('text-area-list-element');
    var field = document.createElement('textarea');
    field.setAttribute('placeholder', 'Enter a comment here and press enter..');

    listElem.appendChild(field);
    parent.appendChild(listElem);

    //The new currentShown container
    currentShown = field;

    //Add event listener for the enter key
    currentShown.addEventListener('keyup',function(e){
        var key = e.keyCode ? e.keyCode : e.which;
        if(key === 10 || key === 13){
            console.log(this.parentNode.parentNode)
            add_comment(this.value, this.parentNode.parentNode, this, params.key);
            this.value = '';
        }
    });

    field.focus();
}

/*
    This is the onload function which adds evenet listeners to
    show inputs for each reply button.
 */
window.onload = function(){

    const form = document.getElementById('comment-form');
    var favoIcon = document.getElementById('favourite-icon');

    form.addEventListener('submit', function(e){
        e.preventDefault();

        var commentText = document.getElementById('comment-field');
        add_comment(commentText.value, 0, 0, params.key);

    });

    var commentElements = document.querySelectorAll('.reply-button');
    for(var i = 0; i<commentElements.length;i++){
        commentElements[i].addEventListener('click', function(){
            show_input(this.parentNode.getAttribute('id'));
        })
    }
};


/**
 * This function creates and appends a new comment element to the comment
 * tree if the comments is successfully added the the database.
 * @param text
 * @param parent_element
 * @param field
 * @param p_key
 */
function add_comment(text, parent_element, field, p_key){
    var root = true;
    var parent_id = null;
    var newParentElement;

    if(parent_element == 0){
        parent_id = 0;
        parent_element = document.getElementById('comments-list');
    }else{
        parent_id = parseInt(parent_element.getAttribute('id'));

        var siblings = field.parentNode.parentNode.parentNode.childNodes;
        if(siblings.length <= 1){
            newParentElement = field.parentNode.parentNode.parentNode;
        }else{
            newParentElement = field.parentNode.parentNode.nextSibling.childNodes[1];
        }
        root = false;
    }

    $.ajax({
        type: "POST",
        data: {commentField:text, parent_id:parent_id, post_key:p_key},
        url: host_url + "comment",
        success: function(data){
            if(data.comment){
                var comment = data.comment;
                var commentElement = document.createElement('LI');
                commentElement.setAttribute('id', comment.id);
                commentElement.classList.add('comment');

                var profileInfo = document.createElement('div');
                profileInfo.classList.add('profile-info');

                var imgWrapper = document.createElement('div');
                imgWrapper.classList.add('profile-pic-wrapper');

                var img = new Image();
                img.classList.add('profile-pic');
                console.log(comment.avatar);
                if(comment.avatar != null){
                    img.src = './avatars/' + comment.avatar;
                }else{
                    img.src = './images/profile.png';
                }


                var profileLink = document.createElement('a');
                profileLink.innerHTML = comment.username;
                profileLink.classList.add('profile-link');
                profileLink.href = '#';

                var commentText = document.createElement('p');
                commentText.classList.add('comment-text');
                commentText.innerHTML = comment.text;

                var replyButton = document.createElement('input');
                replyButton.classList.add('reply-button');
                replyButton.setAttribute('value', 'reply');
                replyButton.setAttribute('type', 'button');

                replyButton.addEventListener('click', function(){
                    show_input(this.parentNode.getAttribute('id'));
                });

                imgWrapper.appendChild(img)
                profileInfo.appendChild(imgWrapper);
                profileInfo.appendChild(profileLink);


                commentElement.appendChild(profileInfo);
                commentElement.appendChild(commentText);
                commentElement.appendChild(replyButton);

                if(root){
                    parent_element.prepend(commentElement);
                }else{
                    var sibling = field.parentNode.parentNode.nextSibling;
                    console.log(sibling)
                    var selfParent = field.parentNode.parentNode;
                    if(sibling == null || sibling.nodeName != 'DETAILS'){
                        var details = document.createElement('details');
                        var summary = document.createElement('summary');
                        summary.innerHTML = '<span class="children-count">1</span> replies';

                        var listContainer = document.createElement('ul');
                        listContainer.classList.add('comment-parent-list');
                        listContainer.appendChild(commentElement);

                        details.appendChild(summary);
                        details.appendChild(listContainer);

                        $(details).insertAfter(selfParent);
                        details.open = true;
                    }else{
                        console.log(sibling.childNodes[0].childNodes[0]);
                        var count = sibling.childNodes[0].childNodes[0];
                        count.innerHTML = parseInt(count.innerHTML) + 1;

                        newParentElement.prepend(commentElement);
                        newParentElement.open = true;
                    }
                }

            }else if(data == '2'){
                alert('You need to login to comment on posts.');
            }
        },
        error:function(data){
            console.log(data.responseText)
        }});
}




