var fixed = false;

//Change the controls on scroll
window.onscroll = function() {
	var rightCont = $('#right-container');
    var fixedCont = $('#fixed-wrapper');
    var footer = $('footer');

    var search = document.getElementById('search-results');

    var diff = 0;
    if(!search){
        diff = 54;
    }

    if(fixedCont.offset().top + fixedCont.height() >= footer.offset().top - diff && !fixed){
    	fixed = true;
        rightCont.css('align-self', 'flex-end');
        fixedCont.css('position', 'absolute');
        fixedCont.css('bottom', '5px');
    }else if($(document).scrollTop() + window.innerHeight <= footer.offset().top + 40  && fixed){
    	fixed = false;
        rightCont.css('align-self', 'auto');
        fixedCont.css('position', 'fixed');
        fixedCont.css('bottom', 'auto');
	}
};


window.onload = function(){
    var main = $('main');
    var footer = $('footer');
    var rightCon = $('#right-container');


    if((main.height() + footer.height()) < window.innerHeight){
        footer.css("position", "absolute");
        footer.css("bottom", "0");
        rightCon.css({'z-index':100})
        $('#fixed-wrapper').css({'opacity':50})
    }

    var links = document.querySelectorAll('.nsfw');
    for(var i = 0;i<links.length;i++){
        links[i].addEventListener('click', function(e){
            e.preventDefault();
        })
    }
};

/**
 * This function shows or hides a post that is marked
 * as a not safe for work post.
 * @param key
 * @param button
 */
function show_nsfw(key, button){
    var img = document.getElementById(key);
    img.classList.toggle('nsfw');
    var link = img.parentNode;
    if(img.classList.contains('nsfw')){
        link.removeEventListener('click', function(e){});

        //Remove action listeners
        elClone = link.cloneNode(true);
        link.parentNode.replaceChild(elClone, link);

        elClone.addEventListener('click', function(e){e.preventDefault();})
        button.style.background = '#FFA5A9';
        button.innerHTML = 'Click to view nsfw post';
    }else{
        elClone = link.cloneNode(true);
        link.parentNode.replaceChild(elClone, link);
        elClone.addEventListener('click',function (e) {
            console.log('works')
            return true;
        });
        button.style.background = '#ADFFA3';
        button.innerHTML = 'Click to hide nsfw post';

    }

}

