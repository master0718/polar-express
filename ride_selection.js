// Declare selectedRides as a global variable once
const selectedRides = new Set();
let totalMinutes = 0;
let hasShownPopup = false; // Prevent multiple popups

// Toggle ride selection
function toggleSelection(slotId, minutes) {
    const checkbox = document.getElementById(`checkbox-${slotId}`);
    if (checkbox.checked) {
        selectedRides.add(slotId);
        totalMinutes += parseInt(minutes, 10);
    } else {
        selectedRides.delete(slotId);
        totalMinutes -= parseInt(minutes, 10);
    }

    const totalHours = (totalMinutes / 60).toFixed(2);
    const commitmentElement = document.getElementById('total-commitment');
    const submitButton = document.getElementById('submit-button');

    commitmentElement.textContent = `Total Time Commitment: ${totalHours} hours`;
    commitmentElement.style.display = totalMinutes > 0 ? 'block' : 'none';
    submitButton.style.display = totalMinutes > 0 ? 'inline-block' : 'none';

    if (totalMinutes >= 360 && !hasShownPopup) { // Popup for 6+ hours
        alert(`You have committed to ${totalHours} hours. Please confirm this commitment.`);
        hasShownPopup = true;
    }
}

// Handle "Submit Selections" button click
function handleSubmitSelections() {
    if (selectedRides.size === 0) {
        alert('Please select at least one ride.');
        return;
    }

    const rideIds = Array.from(selectedRides);
    const modalContent = document.getElementById('modal-content');
    const rideDetails = document.getElementById('ride-details');

    modalContent.innerHTML = `You have selected ${rideIds.length} rides. Please enter your details.`;
    rideDetails.innerHTML = '';

    rideIds.forEach((rideId) => {
        const formGroup = document.createElement('div');
        formGroup.style.marginBottom = '10px';

        const rideLabel = document.createElement('label');
        rideLabel.textContent = `Ride ID: ${rideId}`;
        formGroup.appendChild(rideLabel);

        const partySizeInput = document.createElement('input');
        partySizeInput.type = 'number';
        partySizeInput.id = `party-size-${rideId}`;
        partySizeInput.placeholder = 'Party Size (default: 1)';
        partySizeInput.style.marginLeft = '10px';
        formGroup.appendChild(partySizeInput);

        const notesInput = document.createElement('textarea');
        notesInput.id = `notes-${rideId}`;
        notesInput.placeholder = 'Add any notes (optional)';
        notesInput.style.display = 'block';
        notesInput.style.marginTop = '10px';
        formGroup.appendChild(notesInput);

        rideDetails.appendChild(formGroup);
    });

    document.getElementById('selection-modal').style.display = 'block';
}

// Finalize selections
function finalizeSelections() {
    const rideIds = Array.from(selectedRides);
    const submissionData = [];
    let hasError = false;

    // Collect data for each selected ride
    rideIds.forEach((rideId) => {
        const partySizeInput = document.getElementById(`party-size-${rideId}`);
        const notesInput = document.getElementById(`notes-${rideId}`);
        
        const partySize = partySizeInput ? parseInt(partySizeInput.value, 10) : 1; // Default to 1
        const notes = notesInput ? notesInput.value.trim() : ''; // Default to empty string

        if (!partySize || partySize <= 0) {
            alert(`Invalid party size for ride ${rideId}`);
            hasError = true;
        }

        submissionData.push({
            rideId,
            partySize,
            notes: notes || ''
        });
    });

    if (hasError) {
        return; // Abort submission if there's an error
    }

    // Send the data to the server
    fetch('submit_selections.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(submissionData)
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then((data) => {
            if (data.success) {
                alert('Your selections have been finalized successfully!');
                window.location.reload(); // Optional: Reload the page or navigate to a confirmation screen
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch((error) => {
            console.error('There was a problem with the fetch operation:', error);
            alert('An error occurred while finalizing your selections. Please try again.');
        });
}

// Close the modal
function closeModal() {
    document.getElementById('selection-modal').style.display = 'none';
}