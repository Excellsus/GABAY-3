// Get all room elements - UPDATED VERSION
// Get all groups and paths that might be rooms
const allGroups = document.querySelectorAll('g[id^="g"]');
// Grab both patterns of room IDs based on your updated SVG structure
const allPaths = document.querySelectorAll('path[id^="room"], path[id^="room-"]'); 

// Function to identify real rooms
function identifyRooms() {
    console.log('Identifying room elements...');
    let roomsIdentified = 0;
    
    // First, check if any groups already have data-office-id attributes (set by labelSetup.js)
    const groupsWithOfficeId = document.querySelectorAll('g[data-office-id]');
    if (groupsWithOfficeId.length > 0) {
        console.log(`Found ${groupsWithOfficeId.length} groups with data-office-id attributes`);
        groupsWithOfficeId.forEach(group => {
            group.setAttribute('data-room', 'true');
            console.log(`Marked ${group.id} as a room based on data-office-id`);
            roomsIdentified++;
        });
    }
    
    // Second, check if there are any direct room paths with format room-X-1
    const roomPaths = document.querySelectorAll('path[id^="room-"]');
    if (roomPaths.length > 0) {
        console.log(`Found ${roomPaths.length} paths with room- prefix IDs`);
        roomPaths.forEach(path => {
            const parentGroup = path.closest('g');
            if (parentGroup) {
                parentGroup.setAttribute('data-room', 'true');
                // If the path has an office ID, copy it to the parent group
                if (path.dataset.officeId) {
                    parentGroup.dataset.officeId = path.dataset.officeId;
                }
                console.log(`Marked ${parentGroup.id} as a room based on path with id ${path.id}`);
                roomsIdentified++;
            } else {
                // If the path isn't in a group, mark it directly
                path.setAttribute('data-room', 'true');
                console.log(`Marked path ${path.id} as a room directly`);
                roomsIdentified++;
            }
        });
    }
    
    // Third, check for paths with room prefix but without dash
    const oldFormatRoomPaths = document.querySelectorAll('path[id^="room"]:not([id^="room-"])');
    if (oldFormatRoomPaths.length > 0) {
        console.log(`Found ${oldFormatRoomPaths.length} paths with 'room' prefix IDs (old format)`);
        oldFormatRoomPaths.forEach(path => {
            const parentGroup = path.closest('g');
            if (parentGroup) {
                parentGroup.setAttribute('data-room', 'true');
                if (path.dataset.officeId) {
                    parentGroup.dataset.officeId = path.dataset.officeId;
                }
                console.log(`Marked ${parentGroup.id} as a room based on path with old format id ${path.id}`);
                roomsIdentified++;
            } else {
                path.setAttribute('data-room', 'true');
                console.log(`Marked path ${path.id} as a room directly (old format)`);
                roomsIdentified++;
            }
        });
    }
    
    // Fourth, check for inkscape:label attributes that contain "room"
    allGroups.forEach(group => {
        // Check if this group has a label with "room" in it
        const labelAttr = group.getAttribute('inkscape:label');
        if (labelAttr && labelAttr.toLowerCase().includes('room')) {
            group.setAttribute('data-room', 'true');
            console.log(`Marked ${group.id} as a room based on label: ${labelAttr}`);
            roomsIdentified++;
        }
    });
    
    // Fifth, check for paths with interactive-room class (added by labelSetup.js)
    const interactivePaths = document.querySelectorAll('path.interactive-room');
    if (interactivePaths.length > 0) {
        console.log(`Found ${interactivePaths.length} paths with interactive-room class`);
        interactivePaths.forEach(path => {
            const parentGroup = path.closest('g');
            if (parentGroup) {
                parentGroup.setAttribute('data-room', 'true');
                // If the path has an office ID, copy it to the parent group
                if (path.dataset.officeId) {
                    parentGroup.dataset.officeId = path.dataset.officeId;
                }
                console.log(`Marked ${parentGroup.id} as a room based on interactive-room class`);
                roomsIdentified++;
            }
        });
    }
    
    // Sixth, try to find paths with the expected room IDs directly
    console.log('Trying to find rooms by looking for specific path IDs');
    
    // Explicitly check for paths that match the new pattern room-{X}-1
    const roomPatternPaths = Array.from(document.querySelectorAll('path'))
        .filter(path => path.id && (path.id.match(/room-\d+-\d+/) || path.id.match(/room\d+-\d+/)));
        
    if (roomPatternPaths.length > 0) {
        console.log(`Found ${roomPatternPaths.length} paths with room pattern IDs`);
        roomPatternPaths.forEach(path => {
            const parentGroup = path.closest('g');
            if (parentGroup) {
                parentGroup.setAttribute('data-room', 'true');
                console.log(`Marked ${parentGroup.id} as a room based on path ID ${path.id}`);
                roomsIdentified++;
            } else {
                // If the path isn't in a group, mark it directly
                path.setAttribute('data-room', 'true');
                console.log(`Marked path ${path.id} as a room directly`);
                roomsIdentified++;
            }
        });
    }
    
    // If no rooms were identified by the above methods, fallback to the exclusion method
    if (roomsIdentified === 0) {
        console.warn('No rooms identified by attributes or classes. Falling back to exclusion method.');
        
        // Exclude the known non-room groups - add or modify based on your SVG structure
        const excludedIds = ['g199-8', 'g2-8', 'g176-6', 'g187-3', 'g187-2-0', 'g193-6', 'g196-5'];
        Array.from(allGroups).forEach(group => {
            // Check if this group has a path child - rooms should have path elements
            const hasPath = group.querySelector('path') !== null;
            
            if (hasPath && !excludedIds.includes(group.id)) {
                group.setAttribute('data-room', 'true');
                console.log(`Marked ${group.id} as a room using exclusion method`);
                roomsIdentified++;
            } else if (excludedIds.includes(group.id)) {
                console.log(`Excluded ${group.id} from being a room`);
            }
        });
    }

    // Deduplicate - remove any rooms we may have counted multiple times
    const allRoomGroups = document.querySelectorAll('g[data-room="true"]');
    const allRoomPaths = document.querySelectorAll('path[data-room="true"]');
    
    // Log all identified rooms 
    const identifiedRooms = Array.from(allRoomGroups).concat(Array.from(allRoomPaths));
    console.log(`Total rooms identified: ${identifiedRooms.length} (${allRoomGroups.length} groups, ${allRoomPaths.length} standalone paths)`);
    
    // Debug output - list all identified rooms
    identifiedRooms.forEach((room, index) => {
        console.log(`Identified room ${index+1}: ${room.tagName} #${room.id}`);
    });
    
    return identifiedRooms;
}

// Identify rooms and get only the room elements
const rooms = identifyRooms();

const editButton = document.getElementById('edit-floorplan-btn');
const floorPlanContainer = document.querySelector('.floor-plan-container');
let isEditMode = false;
let isDragging = false;
let isOverRoom = false;
let draggedElement = null;
let startX = 0;
let startY = 0;

console.log('Drag Drop Setup script loaded - UPDATED VERSION');
console.log(`Found ${rooms.length} draggable room elements after classification`);
console.log(`Edit button found: ${editButton !== null}`);

// Log each room ID for debugging
rooms.forEach((room, index) => {
    console.log(`Room ${index+1}: ${room.id}`);
});

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
    console.log('Enabling drag and drop - edit mode activated');
    
    // Add visual indicator for edit mode
    document.body.classList.add('edit-mode-active');
    floorPlanContainer.classList.add('edit-mode-active');
    
    // Display a message to the user
    const editModeMsg = document.createElement('div');
    editModeMsg.id = 'edit-mode-message';
    editModeMsg.className = 'edit-mode-message';
    editModeMsg.textContent = 'Edit Mode: Drag rooms to reposition them';
    floorPlanContainer.appendChild(editModeMsg);
    
    // Make each room draggable
    rooms.forEach(room => {
        // Find the path element inside the group
        const pathElement = room.querySelector('path');
        if (!pathElement) {
            console.warn(`No path element found in room group ${room.id}`);
            return;
        }
        
        room.classList.add('draggable');
        
        // Store original data for later use
        if (!room.dataset.originalId) {
            room.dataset.originalId = room.id;
        }
        
        // Add mouse event listeners
        room.addEventListener('mousedown', handleMouseDown, true);
        room.addEventListener('mouseenter', handleRoomMouseEnter, true);
        room.addEventListener('mouseleave', handleRoomMouseLeave, true);

        console.log(`Added event listeners to room ${room.id}`);
    });
    
    // Disable pan and zoom initially - we'll re-enable it when not over a room
    if (window.panZoom) {
        window.panZoom.disablePan();
        window.panZoom.disableZoom();
        console.log('Pan and zoom temporarily disabled in edit mode');
    }
}

// Function to disable drag and drop
function disableDragAndDrop() {
    console.log('Disabling drag and drop - returning to view mode');
    
    // Remove edit mode indicator
    document.body.classList.remove('edit-mode-active');
    floorPlanContainer.classList.remove('edit-mode-active');
    
    // Remove edit mode message if it exists
    const editModeMsg = document.getElementById('edit-mode-message');
    if (editModeMsg) {
        editModeMsg.remove();
    }
    
    rooms.forEach(room => {
        room.classList.remove('draggable');
        
        // Remove event listeners
        room.removeEventListener('mousedown', handleMouseDown, true);
        room.removeEventListener('mouseenter', handleRoomMouseEnter, true);
        room.removeEventListener('mouseleave', handleRoomMouseLeave, true);
    });
    
    // Re-enable panning and zooming
    if (window.panZoom) {
        window.panZoom.enablePan();
        window.panZoom.enableZoom();
        console.log('Pan and zoom re-enabled in view mode');
    }
}

// Mouse enter/leave handlers for rooms
function handleRoomMouseEnter(e) {
    if (isEditMode) {
        e.stopPropagation();
        isOverRoom = true;
        console.log(`Mouse entered room: ${e.currentTarget.id}`);
        
        // In edit mode, disable pan/zoom when hovering over a room
        if (window.panZoom) {
            window.panZoom.disablePan();
            window.panZoom.disableZoom();
            console.log('Disabled pan/zoom - mouse over room in edit mode');
        } else {
            console.warn('panZoom instance not found on window object');
        }
        
        // Highlight the room to indicate it can be dragged
        e.currentTarget.classList.add('room-hover');
    }
}

function handleRoomMouseLeave(e) {
    if (isEditMode) {
        e.stopPropagation();
        isOverRoom = false;
        console.log(`Mouse left room: ${e.currentTarget.id}`);
        
        // Remove hover highlight
        e.currentTarget.classList.remove('room-hover');
        
        // Only re-enable pan/zoom if we're not currently dragging
        if (window.panZoom && !isDragging) {
            window.panZoom.enablePan();
            window.panZoom.enableZoom();
            console.log('Enabled pan/zoom - mouse left room in edit mode');
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
    draggedElement = e.currentTarget; // Use currentTarget to get the room group, not just the path
    
    console.log(`Started dragging room: ${draggedElement.id}`);
    
    // Add dragging class for visual feedback
    draggedElement.classList.add('dragging');
    
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
        console.log('Disabled pan/zoom for dragging');
    } else {
        console.warn('panZoom instance not found on window object');
    }
}

function handleMouseMove(e) {
    if (!isDragging || !draggedElement) return;
    
    e.stopPropagation();
    e.preventDefault();
    
    // Find room element under cursor
    const elemUnderCursor = document.elementFromPoint(e.clientX, e.clientY);
    const roomUnderCursor = elemUnderCursor ? elemUnderCursor.closest('g[data-room="true"]') : null;
    
    // Remove previous drag-target class from all rooms
    rooms.forEach(room => {
        if (room !== draggedElement) {
            room.classList.remove('drag-target');
        }
    });
    
    // Add visual feedback for potential drop targets
    if (roomUnderCursor && roomUnderCursor !== draggedElement) {
        roomUnderCursor.classList.add('drag-target');
        console.log(`Potential drop target: ${roomUnderCursor.id}`);
    }
}

function handleMouseUp(e) {
    if (!isDragging || !draggedElement) return;
    
    e.stopPropagation();
    e.preventDefault();
    
    isDragging = false;
    window.isDragging = false; // Reset global dragging state
    
    // Reset opacity and remove dragging class
    draggedElement.style.opacity = '1';
    draggedElement.classList.remove('dragging');
    
    // Remove drag-target class from all rooms
    rooms.forEach(room => {
        room.classList.remove('drag-target');
    });
    
    // Remove event listeners
    document.removeEventListener('mousemove', handleMouseMove, true);
    document.removeEventListener('mouseup', handleMouseUp, true);
    
    // Find room element under cursor
    const elemUnderCursor = document.elementFromPoint(e.clientX, e.clientY);
    const dropTarget = elemUnderCursor ? elemUnderCursor.closest('g[data-room="true"]') : null;
    
    console.log(`Drop target: ${dropTarget ? dropTarget.id : 'none'}`);
    
    if (dropTarget && dropTarget !== draggedElement) {
        console.log(`Dropping on target: ${dropTarget.id}`);
        
        // Find the path elements to swap styles
        const draggedPath = draggedElement.querySelector('path');
        const targetPath = dropTarget.querySelector('path');
        
        if (draggedPath && targetPath) {
            // Get the current styles
            const draggedStyle = window.getComputedStyle(draggedPath);
            const targetStyle = window.getComputedStyle(targetPath);
            
            const draggedFill = draggedStyle.fill;
            const targetFill = targetStyle.fill;
            
            console.log('Before swap - Colors:', {
                draggedElement: {
                    id: draggedElement.id,
                    fill: draggedFill
                },
                dropTarget: {
                    id: dropTarget.id,
                    fill: targetFill
                }
            });
            
            // Swap fills
            draggedPath.style.fill = targetFill;
            targetPath.style.fill = draggedFill;
            
            // Swap office IDs if they exist
            if (draggedPath.dataset.officeId && targetPath.dataset.officeId) {
                const draggedOfficeId = draggedPath.dataset.officeId;
                const targetOfficeId = targetPath.dataset.officeId;
                draggedPath.dataset.officeId = targetOfficeId;
                targetPath.dataset.officeId = draggedOfficeId;
                
                console.log(`Swapped office IDs: ${draggedOfficeId} and ${targetOfficeId}`);
            } else {
                console.warn('Office IDs not found on one or both paths');
            }
            
            // Swap labels
            const draggedText = draggedElement.querySelector('text');
            const targetText = dropTarget.querySelector('text');
            
            if (draggedText && targetText) {
                // Get all tspans in each text element
                const draggedTspans = draggedText.querySelectorAll('tspan');
                const targetTspans = targetText.querySelectorAll('tspan');
                
                // Store the text content
                const draggedContent = Array.from(draggedTspans).map(tspan => tspan.textContent);
                const targetContent = Array.from(targetTspans).map(tspan => tspan.textContent);
                
                console.log('Swapping text content:', {
                    from: draggedContent,
                    to: targetContent
                });
                
                // Swap content
                if (draggedTspans.length && targetTspans.length) {
                    for (let i = 0; i < Math.min(draggedTspans.length, targetTspans.length); i++) {
                        draggedTspans[i].textContent = targetContent[i] || '';
                        targetTspans[i].textContent = draggedContent[i] || '';
                    }
                } else {
                    // Fallback to swapping entire text content
                    const draggedContent = draggedText.textContent;
                    const targetContent = targetText.textContent;
                    draggedText.textContent = targetContent;
                    targetText.textContent = draggedContent;
                }
            } else {
                console.warn('Text elements not found for swapping');
            }
        } else {
            console.warn('Path elements not found for swapping styles');
        }
    }
    
    // Re-enable panning if not over a room
    if (window.panZoom && !isOverRoom) {
        window.panZoom.enablePan();
        window.panZoom.enableZoom();
        console.log('Re-enabled pan/zoom after drop');
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
        
        // --- NEW: Save room assignments ---
        // Collect current room-label assignments
        const assignments = [];
        const processedRooms = new Set(); // Track processed rooms to prevent duplicates
        
        document.querySelectorAll('g[data-room="true"]').forEach(group => {
            const room = group.querySelector('path');
            const label = group.querySelector('text');
            
            if (room && !processedRooms.has(group.id)) {
                // Try to get office ID from data attribute or set a default
                const officeId = room.dataset.officeId || group.id.replace(/\D/g, '');
                
                if (!officeId) {
                    console.error('No office ID found for room:', group.id);
                    return;
                }
                
                processedRooms.add(group.id); // Mark this room as processed
                
                // Get text content if label exists
                let labelText = "";
                if (label) {
                    if (label.querySelectorAll('tspan').length > 0) {
                        const tspans = label.querySelectorAll('tspan');
                        labelText = Array.from(tspans).map(tspan => tspan.textContent).join(' ');
                    } else {
                        labelText = label.textContent.trim();
                    }
                }
                
                console.log('Found room:', {
                    roomId: group.id,
                    label: labelText || 'No label',
                    officeId: officeId
                });
                
                assignments.push({
                    roomId: group.id,
                    label: labelText || 'Room ' + officeId,
                    officeId: officeId
                });
            }
        });
        
        if (assignments.length === 0) {
            console.error('No valid assignments found');
            alert('No valid room assignments found to save.');
            return;
        }
        
        console.log('Sending assignments to server:', assignments);
        
        // Send assignments to the server - use savePositions.php instead of saveOffice.php
        fetch('savePositions.php', {
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

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded in dragDropSetup.js');
    
    // Check if panZoom object exists in window scope
    if (!window.panZoom) {
        console.warn('panZoom object not found in window scope. Wait for it to be available.');
        
        // Set a small delay to check again
        setTimeout(() => {
            if (window.panZoom) {
                console.log('panZoom object found after delay');
            } else {
                console.error('panZoom object still not available. Check script load order.');
            }
        }, 1000);
    } else {
        console.log('panZoom object found in window scope');
    }
});