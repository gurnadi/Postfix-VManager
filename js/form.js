<script type="text/javascript">

submitData = function(formObject){
                            $('#img_load').show();
		 	    $('.help-inline').hide();
                            $.post(formObject.action,$(formObject).serialize(),function (response){

                                data = response;//JSON.parse(response);
				page = response.page;

			        if(data.response==false){
                                    var z=0;
                                    $.each(data.errors, function(index,val){
                                        promptError(index,val);
                                        if(z==0){
                                            $("body").scrollTo($('#'+index), 500, {'axis' : 'y', 'over': -4});
                                            $('#'+index).focus();
                                        }
                                        z++;
                                    });
                                $('#img_load').hide();
                                }
				else
				{
				  if(page == '/sendemail')
				  {
				    alert('Email sucessfully sent');
				    window.location='/';
				  }
				  else
				  {
				    window.location=page;
				  }
				}
                            }
                        );}

                       function promptError (object, error) {
                            $('#'+object).parent().find("span.help-inline").remove();
                            $('#'+object).parent().append("<span class='help-inline'>" + error + "</span>")

                            $('#'+object).one("keypress", function () {
                                $(this).parent().find("span.help-inline").remove();
                            })
                        }

$('a,button').focus(function() {
  $(this).css('outline','none');
});

$('input').focus(function() {
  $(this).css('outline','none');
});

</script>
