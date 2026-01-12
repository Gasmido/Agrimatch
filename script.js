  function countCharacters(textarea) {
            const maxLengths = 1000;
            const currentLength = textarea.value.length;
            
            // Get the character counter element
            const charCountElement = document.getElementById('charCount');
            
            // Update the character counter
            const remainingCharacters = maxLengths - currentLength;
            charCountElement.textContent = remainingCharacters + ' characters remaining';
            console.log(maxLengths);

            // Add a warning class if characters exceed the limit
            if (remainingCharacters < 0) {
                charCountElement.classList.add('warning');
                textarea.value = textarea.value.substring(0, maxLengths); 
                charCountElement.textContent = '0 characters remaining';
            } else {
                charCountElement.classList.remove('warning');
            }
        }