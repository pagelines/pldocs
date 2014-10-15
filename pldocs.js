!function ($) {

	$(document).ready(function() {

		init()

		populateSidebarData()

		setStickySidebar()

		CheckDocsSelect()

		HideShowli()

		function init() {
			$('.pldoc-dropdowns').chosen()
			$('.pldoc-dropdowns-search').chosen()
			$('.pldocs-show-search').click(function(){
				$('.pldocs-search').slideToggle(150)
			})
			$(window).resize(function(){
				$('.pldocs-sidebar').sticky('update')
			})
			$('.pldocs-mobile-drop').on('click', function(){
				var theList = $(this).next()
				if( theList.hasClass('show-me') )
					theList.removeClass('show-me')
				else
					theList.addClass('show-me')
			})
		}

		function setStickySidebar(){

			$('.pldocs-wrapper').each(function(){
				var docHeight = $(document).height()
				var stdOffset = 20
				,	theWrapper = $(this)
				,	theSidebar = theWrapper.find('.pldocs-sidebar')
				,	sidebarTopOff = $('.pl-fixed-top').height() + theSidebar.position().top + $('#wpadminbar').height() + stdOffset
				,	sidebarBottomOff = docHeight + stdOffset*2 - theWrapper.offset().top - theWrapper.height()

				theSidebar.sticky({
							topSpacing: sidebarTopOff
							, bottomSpacing: sidebarBottomOff
						})
			})
		}

		function CheckDocsSelect() {
			$('.pldoc-dropdowns').change(function() {
				var url = $(this).val()
				window.location.href = url
			})
		}

		function HideShowli() {
			$('.sub').hide()
			$('.sub.heading1').show()
			$('.theme-list-nav').visualNav();
			$('.main a').click( function() {
				$('.sub').hide()
				var clicked = $(this).parent().attr('id')
				$('.sub.' + clicked).slideToggle()
			})
		}

		function populateSidebarData() {

				var counter = 0;
				var headings = 0;

				$('.pldocs-content h2').each(function(index, value) {
					headings = headings + 1
					counter = counter + index
					$(this).attr('id', 'section' + counter )
					var content = sprintf( '<li id="heading%s" class="main"><a href="#section%s">%s</a></li>', headings, counter, $(this).text() )
					$(".theme-list-nav").append(content);

					$(this).nextUntil('h2').each(function(index, value) {

						var el = $(this).get(0).tagName
						if( 'H3' == el ) {
							counter = counter + index
							$(this).attr('id', 'section' + counter )
							var content = sprintf( '<li class="sub heading%s"><a href="#section%s">%s</a></li>', headings, counter, $(this).text() )
							$(".theme-list-nav").append(content);
						}
					})
				})
		}

	})
}(window.jQuery);
