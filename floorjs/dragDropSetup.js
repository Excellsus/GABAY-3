// Get all room elements
const rooms = document.querySelectorAll('[id^="room-"]');
const editButton = document.getElementById('edit-floorplan-btn');
let isEditMode = false;
let isDragging = false;
let isOverRoom = false;
let draggedElement = null;
let startX = 0;
let startY = 0;

// Function to create a ghost image for dragging
function createGhostImage(element) {
    const ghost = element.cloneNode(true);
    ghost.style.position = 'absolute';
    ghost.style.pointerEvents = 'none';
    ghost.style.opacity = '0.5';
    ghost.style.fill = '#1A5632';
    ghost.style.stroke = '#1A5632';
    ghost.style.strokeWidth = '2';
    ghost.style.filter = 'drop-shadow(0 0 4px rgba(0,0,0,0.3))';
    document.body.appendChild(ghost);
    return ghost;
}

// Function to enable drag and drop
function enableDragAndDrop() {
    console.log('Enabling drag and drop');
    rooms.forEach(room => {
        room.style.cursor = 'move';
        
        // Add mouse event listeners
        room.addEventListener('mousedown', handleMouseDown, true);
        room.addEventListener('mouseenter', handleRoomMouseEnter, true);
        room.addEventListener('mouseleave', handleRoomMouseLeave, true);
    });
}

// Function to disable drag and drop
function disableDragAndDrop() {
    console.log('Disabling drag and drop');
    rooms.forEach(room => {
        room.style.cursor = 'default';
        
        // Remove event listeners
        room.removeEventListener('mousedown', handleMouseDown, true);
        room.removeEventListener('mouseenter', handleRoomMouseEnter, true);
        room.removeEventListener('mouseleave', handleRoomMouseLeave, true);
    });
}

// Mouse enter/leave handlers for rooms
function handleRoomMouseEnter(e) {
    if (isEditMode) {
        e.stopPropagation();
        isOverRoom = true;
        if (window.panZoom) {
            window.panZoom.disablePan();
            window.panZoom.disableZoom();
        }
    }
}

function handleRoomMouseLeave(e) {
    if (isEditMode) {
        e.stopPropagation();
        isOverRoom = false;
        if (window.panZoom && !isDragging) {
            window.panZoom.enablePan();
            window.panZoom.enableZoom();
        }
    }
}

// Mouse event handlers
function handleMouseDown(e) {
    if (!isEditMode) return;
    
    e.stopPropagation();
    e.preventDefault();
    
    isDragging = true;
    window.isDragging = true; // Set global dragging state
    draggedElement = e.target;
    
    // Get the initial mouse position
    startX = e.clientX;
    startY = e.clientY;
    
    // Make the dragged element semi-transparent
    draggedElement.style.opacity = '0.5';
    
    // Add event listeners for dragging
    document.addEventListener('mousemove', handleMouseMove, true);
    document.addEventListener('mouseup', handleMouseUp, true);
    
    // Ensure panning is disabled while dragging
    if (window.panZoom) {
        window.panZoom.disablePan();
        window.panZoom.disableZoom();
    }
}

function handleMouseMove(e) {
    if (!isDragging || !draggedElement) return;
    
    e.stopPropagation();
    e.preventDefault();
    
    // Add visual feedback for potential drop targets
    const dropTarget = document.elementFromPoint(e.clientX, e.clientY)?.closest('[id^="room-"]');
    if (dropTarget && dropTarget !== draggedElement) {
        dropTarget.style.outline = '2px dashed #1A5632';
    } else {
        rooms.forEach(room => {
            if (room !== draggedElement) {
                room.style.outline = 'none';
            }
        });
    }
}

function handleMouseUp(e) {
    if (!isDragging || !draggedElement) return;
    
    e.stopPropagation();
    e.preventDefault();
    
    isDragging = false;
    window.isDragging = false; // Reset global dragging state
    
    // Reset opacity
    draggedElement.style.opacity = '1';
    
    // Remove outline from all rooms
    rooms.forEach(room => {
        room.style.outline = 'none';
    });
    
    // Remove event listeners
    document.removeEventListener('mousemove', handleMouseMove, true);
    document.removeEventListener('mouseup', handleMouseUp, true);
    
    // Check for drop target
    const dropTarget = document.elementFromPoint(e.clientX, e.clientY)?.closest('[id^="room-"]');
    if (dropTarget && dropTarget !== draggedElement) {
        console.log('Dropping on target:', dropTarget.id);
        
        // Get the colors
        const draggedColor = draggedElement.getAttribute('style').match(/fill:(.*?);/)[1];
        const targetColor = dropTarget.getAttribute('style').match(/fill:(.*?);/)[1];
        
        console.log('Before swap - Colors:', {
            draggedElement: {
                id: draggedElement.id,
                color: draggedColor
            },
            dropTarget: {
                id: dropTarget.id,
                color: targetColor
            }
        });
        
        // Swap the colors
        if (draggedColor && targetColor) {
            // Update the style attribute with the new colors
            draggedElement.setAttribute('style', draggedElement.getAttribute('style').replace(draggedColor, targetColor));
            dropTarget.setAttribute('style', dropTarget.getAttribute('style').replace(targetColor, draggedColor));
            
            // Swap the data-office-id attributes
            const draggedOfficeId = draggedElement.dataset.officeId;
            const targetOfficeId = dropTarget.dataset.officeId;
            draggedElement.dataset.officeId = targetOfficeId;
            dropTarget.dataset.officeId = draggedOfficeId;
            
            console.log('After swap - Colors and IDs:', {
                draggedElement: {
                    id: draggedElement.id,
                    color: draggedElement.getAttribute('style').match(/fill:(.*?);/)[1],
                    officeId: draggedElement.dataset.officeId
                },
                dropTarget: {
                    id: dropTarget.id,
                    color: dropTarget.getAttribute('style').match(/fill:(.*?);/)[1],
                    officeId: dropTarget.dataset.officeId
                }
            });
            
            // Get the parent group elements
            const draggedGroup = draggedElement.closest('g');
            const targetGroup = dropTarget.closest('g');
            
            if (draggedGroup && targetGroup) {
                // Get the text elements (they are siblings of the paths)
                const draggedLabel = draggedGroup.querySelector('text');
                const targetLabel = targetGroup.querySelector('text');
                
                // Swap the labels
                if (draggedLabel && targetLabel) {
                    const draggedText = draggedLabel.textContent;
                    const targetText = targetLabel.textContent;
                    
                    console.log('Swapping labels:', {
                        from: draggedText,
                        to: targetText
                    });
                    
                    draggedLabel.textContent = targetText;
                    targetLabel.textContent = draggedText;
                }
            }
        }
    }
    
    // Re-enable panning if not over a room
    if (window.panZoom && !isOverRoom) {
        window.panZoom.enablePan();
        window.panZoom.enableZoom();
    }
    
    draggedElement = null;
}

// Toggle edit mode
editButton.addEventListener('click', (e) => {
    console.log('Edit button clicked');
    // Prevent the click if we're currently dragging
    if (isDragging) {
        e.preventDefault();
        return;
    }
    
    isEditMode = !isEditMode;
    if (isEditMode) {
        editButton.textContent = 'Save';
        enableDragAndDrop();
    } else {
        editButton.textContent = 'Edit';
        disableDragAndDrop();
        // Re-enable panning when exiting edit mode
        if (window.panZoom) {
            window.panZoom.enablePan();
            window.panZoom.enableZoom();
        }
        // --- NEW: Save room assignments ---
        // Collect current room-label assignments
        const assignments = [];
        const processedRooms = new Set(); // Track processed rooms to prevent duplicates
        
        document.querySelectorAll('g').forEach(group => {
            const room = group.querySelector('path[id^="room-"]');
            const label = group.querySelector('text');
            if (room && label && !processedRooms.has(room.id)) {
                const officeId = room.dataset.officeId; // Get office ID from data attribute
                if (!officeId) {
                    console.error('No office ID found for room:', room.id);
                    return;
                }
                processedRooms.add(room.id); // Mark this room as processed
                console.log('Found room:', {
                    roomId: room.id,
                    label: label.textContent.trim(),
                    officeId: officeId
                });
                assignments.push({
                    roomId: room.id, // e.g., 'room-1'
                    label: label.textContent.trim(),
                    officeId: officeId // Include the office ID
                });
            }
        });
        
        if (assignments.length === 0) {
            console.error('No valid assignments found');
            alert('No valid room assignments found to save.');
            return;
        }
        
        console.log('Sending assignments to server:', assignments);
        
        // Send assignments to the server
        fetch('saveOffice.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ assignments })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                alert('Room positions saved successfully!');
                // Refresh the page to show updated positions
                window.location.reload();
            } else {
                alert('Failed to save room positions: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            alert('Error saving room positions: ' + err);
        });
        // --- END NEW ---
    }
});