/**
Vertigo Tip by www.vertigo-project.com
Requires jQuery
*/

this.vtip = function() {
	this.xOffset = -190; // x distance from mouse
	this.yOffset = 25; // y distance from mouse
	
	$(".vtip").hover(
		function(e) {
			this.t = this.title;
			this.title = ''; 
			this.top = (e.pageY + yOffset); this.left = (e.pageX + xOffset);
			$('body').append( '<p id="vtip"><span id="vtipArrow"></span>' + this.t + '</p>' );
			$('p#vtip').css("top", this.top+"px").css("left", this.left+"px").fadeIn("slow");
		},
		function() {
			if (this.t) this.title = this.t;
			$("p#vtip").fadeOut("slow").remove();
		}
	).mousemove(
		function(e) {
			this.top = (e.pageY + yOffset);
			this.left = (e.pageX + xOffset);
			$("p#vtip").css("top", this.top+"px").css("left", this.left+"px");
		}
	);
};
jQuery(document).ready(function($){vtip();}) 