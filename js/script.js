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
    if(main.height() < window.innerHeight){
        footer.css("position", "absolute");
        footer.css("bottom", "0")
        $('#right-container').css('display', 'none')
    }
};


