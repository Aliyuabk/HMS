<!-- start: MAIN JAVASCRIPTS -->
		<script src="vendor/jquery/jquery.min.js"></script>
		<script src="vendor/bootstrap/js/bootstrap.min.js"></script>
		<script src="vendor/modernizr/modernizr.js"></script>
		<script src="vendor/jquery-cookie/jquery.cookie.js"></script>
		<script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
		<script src="vendor/switchery/switchery.min.js"></script>
		<!-- end: MAIN JAVASCRIPTS -->
		<!-- start: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script src="vendor/maskedinput/jquery.maskedinput.min.js"></script>
		<script src="vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
		<script src="vendor/autosize/autosize.min.js"></script>
		<script src="vendor/selectFx/classie.js"></script>
		<script src="vendor/selectFx/selectFx.js"></script>
		<script src="vendor/select2/select2.min.js"></script>
		<script src="vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
		<script src="vendor/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<!-- start: CLIP-TWO JAVASCRIPTS -->
		<script src="assets/js/main.js"></script>
		<!-- start: JavaScript Event Handlers for this page -->
		<script src="assets/js/form-elements.js"></script>
		<script>
			jQuery(document).ready(function() {
				Main.init();
				FormElements.init();
			});
		</script>
		<!-- end: JavaScript Event Handlers for this page -->
		<!-- end: CLIP-TWO JAVASCRIPTS -->	
		 
<script>
$(document).ready(function() {
    $('#doctorSigninForm').on('submit', function(e) {
        e.preventDefault();
        let ehr_no = $('#ehr_no').val().trim();
        $('#signinError').hide();

        if(ehr_no.length !== 6){
            $('#signinError').text('EHR No must be 6 digits').show();
            return;
        }

        $.ajax({
            url: 'p-d.php',
            type: 'POST',
            data: { ehr_no: ehr_no },
            success: function(response){
                response = response.trim();
                if(response === '' || response === '0'){
                    $('#signinError').text('Patient not found').show();
                } else {
                    // Redirect to payment request page with patient EHR
                    window.location.href = 'reception-payment-request.php?ehr_no=' + ehr_no;
                }
            },
            error: function(){
                $('#signinError').text('Server error, try again').show();
            }
        });
    });
});
</script>	
	