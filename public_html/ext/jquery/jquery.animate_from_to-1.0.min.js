/*
 * jQuery Animate From To plugin 1.0
 *
 * Copyright (c) 2011 Emil Stenstrom <http://friendlybit.com>
 *
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
(function(b){b.fn.animate_from_to=function(d,c){return this.each(function(){a(this,d,c)})};b.extend({animate_from_to:a});function a(d,i,l){var c=b(d).eq(0),h=b(i).eq(0);var f={pixels_per_second:1000,initial_css:{background:"#dddddd",opacity:0.8,position:"absolute",top:c.offset().top,left:c.offset().left,height:c.height(),width:c.width(),"z-index":100000},callback:function(){return}};if(l&&l.initial_css){l.initial_css=b.extend({},f.initial_css,l.initial_css)}l=b.extend({},f,l);var k=c.offset().top+c.width()/2-h.offset().top,m=c.offset().left+c.height()/2-h.offset().left,g=Math.floor(Math.sqrt(Math.pow(m,2)+Math.pow(k,2))),e=(g/l.pixels_per_second)*1000,j=b("<div></div>").css(l.initial_css).appendTo("body").animate({top:h.offset().top,left:h.offset().left,height:h.innerHeight(),width:h.innerWidth()},{duration:e}).animate({opacity:0},{duration:100,complete:function(){j.remove();return l.callback()}})}})(jQuery);