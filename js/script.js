function getPosition(element) {
    var xPosition = 0;
    var yPosition = 0;

    while(element) {
        xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
        yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
        element = element.offsetParent;
    }
    return { x: xPosition, y: yPosition };
}

var fixed = false;

window.onscroll = function() {
	var rightCont = $('#right-container');
    var fixedCont = $('#fixed-wrapper');
    var footerY = getPosition(document.querySelector('footer')).y;

    if(footerY <= 578 && !fixed){
    	fixed = true;
        rightCont.css('align-self', 'flex-end');
        fixedCont.css('position', 'absolute');
        fixedCont.css('bottom', '5px');
    }else if(footerY > 578 && fixed){
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
        rightCon.css({'position':'absolute','right':'0', 'z-index':999, 'top':'-43px'})
    }

    var links = document.querySelectorAll('.nsfw');
    for(var i = 0;i<links.length;i++){
        links[i].addEventListener('click', function(e){e.preventDefault();})
    }
};

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

