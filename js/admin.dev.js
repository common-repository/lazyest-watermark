(function($) {
	
	function getGoogleChart() {	
		var wmType = $('#watermark_type').val();
		var wtColor = $('#watermark_color').val().substr(1);
		var wtSize  = $('#watermark_textsize').val();
		var wtAlign = $('#watermark_textalignment').val();
		var woColor = $('#watermark_outlinecolor').val().substr(1);
		var wtWeight = $('#watermark_textweight').val();
		var wmText =  $('#watermark_text').val();
		var wbColor = $('#watermark_bordercolor').val().substr(1);
		var wIconPos = $('#watermark_iconposition').val();							
		var wIconName = $('#watermark_iconname').val();
		var wIconSize = $('#watermark_iconsize').val();
		var noteType = $('#watermark_notetype').val();
		var noteSize = $('#watermark_notesize').val();
		var chartSrc;
				
		if ( wmType == 'd_text_outline') {
			wmText = wmText.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '%20' + '$2');
			chartSrc = wmType+'&chld='+wtColor+'|'+wtSize+'|'+wtAlign+'|'+woColor+'|'+wtWeight+'|'+wmText;
		}
		
		if ( wmType == 'd_simple_text_icon'){
			wmText = wmText.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '%20' + '$2');
			chartSrc = wmType+wIconPos+'&chld='+wmText+'|'+wtSize+'|'+wtColor+'|'+wIconName+'|'+wIconSize+'|'+wtColor+'|'+wbColor;
		}
		
		if ( wmType == 'd_fnote') { 
			wmText = wmText.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '|' + '$2');	
			chartSrc = wmType+'&chld='+noteType+'|'+noteSize+'|'+wtColor+'|'+wtAlign+'|'+wmText;
		}		
		$('#watermark_example').attr( 'src', 'http://chart.apis.google.com/chart?chst='+chartSrc ).load(function(){
			setTimeout(function(){					
				watermarkPosition();
    	}, 200);
		});		
	}
	
	function getOriginalWidth( src ) {
		var t = new Image();
    t.src = src;
    return t.width;
	}
		
	function getOriginalHeight( src ) {
		var t = new Image();
    t.src = src;
    return t.height;
	}
	
	function watermarkPosition() {	
		
		var fullHeight = $('#example_cell').height();			
		var fullWidth =  $('#example_cell').width();		

		var exWidth = Math.min( fullWidth, getOriginalWidth( $('#watermark_example').attr('src') ) );
		var exHeight = Math.min( fullHeight, getOriginalHeight( $('#watermark_example').attr('src') ) );
				
		var fromPosition = $('#position_tr input[type=radio]:checked').val();			
		if ( 'coverimage' ==  fromPosition ) {
			var oAspect = fullWidth / fullHeight;
			var wAspect = exWidth / exHeight;
			if ( oAspect > wAspect ) {
				exWidth = Math.round( fullHeight / exHeight * exWidth );
				exHeight = fullHeight; 
			} else {
				exHeight = Math.round( fullWidth / exWidth * exHeight );
				exWidth = fullWidth;
			}
		}
					
		var topPosition = 0;
		var leftPosition = 0;
		var middlePos = Math.round( fullHeight / 2 );
		var centerPos=  Math.round( fullWidth / 2 ); 
		switch ( fromPosition ) {
			case 'topleft' :
				break;
			case 'topright' :
				leftPosition = fullWidth - exWidth;
				break;
			case 'middleleft' :
				topPosition = middlePos - Math.round( exHeight / 2 );
				break;	
			case 'coverimage' :	
			case 'centered' :
				topPosition = middlePos - Math.round( exHeight / 2 );
				leftPosition = centerPos - Math.round(exWidth / 2 );
				break;
			case 'middleright' :
				topPosition = middlePos - Math.round( exHeight / 2 );
				leftPosition = fullWidth - exWidth;
				break;
			case 'bottomleft' :
				topPosition = fullHeight - exHeight;
				break;
			case 'bottomright' :
				topPosition = fullHeight - exHeight;
				leftPosition = fullWidth - exWidth;
				break;
		}					
		var exOpacity = $('#watermark_transparency').val() / 100;
		$('#watermark_example').css('width',exWidth+'px').css('height', exHeight+'px');
		$('#watermark_example').css('top',topPosition+'px').css('left',leftPosition+'px');
		$('#watermark_example').css('opacity', exOpacity );				
	}
	
	function watermarkDrawExample() {
		getGoogleChart();
	} 		
	
	function watermarkFixed() {
		var topPos = Math.round( $(window).height() / 2 ) - Math.round( $('#example_cell').height() /2  );
		var leftPos = Math.round( $(window).width() / 2 ) - Math.round( $('#example_cell').width() / 2 );
		$('#example_cell').css('top', topPos ).css('left',leftPos);
	}
	
	$(document).ready(function(){
						
		if ( ! $('#example_cell').length )
			return;
		
		watermarkFixed();
		watermarkDrawExample();				
		
		$('#lgw-enable').change(function(){
			if ( $('#lgw-enable').is(':checked') ) {
				$('#lgw-settings').show();
			}
			else 
				$('#lgw-settings').hide();
		});
		
		$('#watermark_type').change(function(){
			showTR = '#' + $(this).val();
			$('.xtra_options').hide(0, function(){				
				$(showTR).show(0);
			}); 
			if ( 'd_fnote' == $(this).val() )
				$('#font_size_p').hide();
			else	
				$('#font_size_p').show();
			watermarkDrawExample();
		});
		
		$('#preview_button').click( function(e){
			$('#example_cell').show();
			(e).preventDefault();
			return false;
		});				
		
		$('#example_cell').click(function(){
			$('#example_cell').hide();
		});
		
		$('#position_tr input[type=radio]').change(function(){
			$('#position_tr input[type=radio]').parent().removeClass( 'selected' );
			$('#watermark_position').removeClass('selected');
			if ( $(this).val() != 'coverimage' ) {
				if ( $(this).is(':checked') )
					$(this).parent().addClass( 'selected' );
				else	
					$(this).parent().removeClass( 'selected' );
			} else {				
				if ( $(this).is(':checked') )
					$('#watermark_position').addClass('selected');
			}
			watermarkPosition();	
		});
		
		$('#watermark_textsize').change(function(){
			exampleLoaded = false;
			watermarkDrawExample();
		});
		
		$('#textcolorpicker').farbtastic('#watermark_color');
		$('#outlinecolorpicker').farbtastic('#watermark_outlinecolor');
		$('#bordercolorpicker').farbtastic('#watermark_bordercolor');
		$('#watermark_color').click(function(e){
			$('#textcolorpicker').show();
		});
		$('#watermark_outlinecolor').click(function(e){
			$('#outlinecolorpicker').show();
		});
		$('#watermark_bordercolor').click(function(e){
			$('#bordercolorpicker').show();
		});
		
		$('#watermark_color').keyup( function() {
			var a = $('#watermark_color').val(),
				b = a;
			a = a.replace(/[^a-fA-F0-9]/, '');
			if ( '#' + a !== b )
				$('#watermark_color').val(a);
			if ( a.length === 3 || a.length === 6 )
				textColor( '#' + a );
		});
		
		$('#watermark_outlinecolor').keyup( function() {
			var a = $('#watermark_outlinecolor').val(),
				b = a;
			a = a.replace(/[^a-fA-F0-9]/, '');
			if ( '#' + a !== b )
				$('#watermark_outlinecolor').val(a);
			if ( a.length === 3 || a.length === 6 )
				outlineColor( '#' + a );
		});
		
		$('#watermark_bordercolor').keyup( function() {
			var a = $('#watermark_bordercolor').val(),
				b = a;
			a = a.replace(/[^a-fA-F0-9]/, '');
			if ( '#' + a !== b )
				$('#watermark_bordercolor').val(a);
			if ( a.length === 3 || a.length === 6 )
				borderColor( '#' + a );
		});
		
		$(document).mouseup( function(e) {
			$('#textcolorpicker').hide();				
			$('#outlinecolorpicker').hide();		
			$('#bordercolorpicker').hide();
			$('#example_cell').hide();
			watermarkDrawExample();
		});
				
		$('#watermark_textalignment').change(function(){
			watermarkDrawExample();
		});
		
		$('#watermark_textweight').change(function(){
			watermarkDrawExample();
		});
		
		$('#watermark_transparency').change(function() {
			watermarkPosition();
		});
		
		$('.watermarker').click( function(){
			return false;
		});
		
	}); // $(document).ready
	
})(jQuery)