function getScript(url, success) {
    var script = document.createElement('script');
    script.src = url;
    var head = document.getElementsByTagName('head')[0],
            done = false;
    // Attach handlers for all browsers
    script.onload = script.onreadystatechange = function() {
        if (!done && (!this.readyState
                || this.readyState == 'loaded'
                || this.readyState == 'complete')) {
            done = true;
            success();
            script.onload = script.onreadystatechange = null;
            head.removeChild(script);
        }
    };
    head.appendChild(script);
}

Joomla.submitbutton = function(task)
{
	if (task == '')
	{
		return false;
	}
	else
	{
		var isValid=true;
		var action = task.split('.');
		if (action[1] != 'cancel' && action[1] != 'close')
		{
			var forms = $$('form.form-validate');
			for (var i=0;i<forms.length;i++)
			{
				if (!document.formvalidator.isValid(forms[i]))
				{
					isValid = false;
					break;
				}
			}
		}
	
		if (isValid)
		{
			Joomla.submitform(task);
			return true;
		}
		else
		{
			alert(Joomla.Text._('COM_GAUSERS_ERROR_UNACCEPTABLE',
			                     'Some values are unacceptable'));
			return false;
		}
	}
}
