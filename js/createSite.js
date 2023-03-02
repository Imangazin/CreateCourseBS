var termSelect = document.getElementById("siteTerm");
var createFeedback = document.getElementById("createSiteResponse");
  $(document).ready(function() {
    $('#createSiteForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: 'createSite.php',
            data: $(this).serialize(),
            dataType: "json",
            success: function(response)
            {
               console.log(response);
               if (response.Code == 200){
                  createFeedback.className = 'alert alert-success';
		  siteName = response.Name;
                  createFeedback.innerHTML = "<a href='https://brocktest.brightspace.com/d2l/home/"+response.OrgUnitId+"' target='_blank'>"+siteName.substring(0,20)+"</a> successfully created";
                  createFeedback.focus();
                  document.getElementById("createSiteForm").reset();
               }
               else {
                  createFeedback.className = 'alert alert-danger';
                  createFeedback.innerHTML = response;
                  createFeedback.focus();
               }
            }
        });
      });
  });

function populateTerm(){
	var termName = [" Fall/Winter"," Spring", " Summer", " Fall/Winter"];
	var termCode = ["-FW", "-SP", "-SU", "-FW"];
	var cYear = new Date().getFullYear();
	//var cMonth 1;
	var cMonth = new Date().getMonth();
	
	if (cMonth<4){
		termSelect[termSelect.length] = new Option(cYear-1+termName[0], cYear-1+termCode[0]);
		for(var i=1; i<termCode.length-1; i++){
			termSelect[termSelect.length] = new Option(cYear+termName[i], cYear+termCode[i]);	
		}
	}else if (cMonth>3 && cMonth<8){
		termSelect[termSelect.length] = new Option(cYear+termName[1], cYear+termCode[1]);
		for(var i=2; i<termCode.length; i++){
			termSelect[termSelect.length] = new Option(cYear+termName[i], cYear+termCode[i]);	
		}
	}else{
		termSelect[termSelect.length] = new Option(cYear+termName[0], cYear+termCode[0]);
		//for(var i=1; i<termCode.length-1; i++){
		//	termSelect[termSelect.length] = new Option(cYear+1+termName[i], cYear+1+termCode[i]);	
		//}
	}
	
}

window.addEventListener('load', function () {
    populateTerm();
});
