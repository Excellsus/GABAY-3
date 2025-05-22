document.addEventListener("DOMContentLoaded", function () {
  console.log("labelSetup.js loaded and DOM ready.");

  const officeDetailsPanel = document.getElementById("office-details-panel");
  const panelOfficeName = document.getElementById("panel-office-name");
  const officeActiveToggle = document.getElementById("office-active-toggle");
  const officeStatusText = document.getElementById("office-status-text");
  const closePanelBtn = document.getElementById("close-panel-btn");
  const tooltip = document.getElementById("floorplan-tooltip");

  let currentSelectedOffice = null;
  let officeActiveStates = {};

  // Log elements found for debugging
  console.log("Office details panel found:", !!officeDetailsPanel);
  console.log("Panel office name found:", !!panelOfficeName);
  console.log("Office active toggle found:", !!officeActiveToggle);
  console.log("Office status text found:", !!officeStatusText);
  console.log("Close panel button found:", !!closePanelBtn);
  console.log("Tooltip found:", !!tooltip);

  function updateRoomAppearanceById(officeId, isActive) {
    // Look up the office in officesData to get its location
    const office = officesData.find(o => o.id.toString() === officeId.toString());
    if (!office) {
      console.warn(`Office with ID ${officeId} not found in officesData`);
      return;
    }
    
    const locationStr = office.location || '';
    
    // Try multiple ways to find the room group
    let roomGroup = null;
    let roomElement = null;
    
    // Try by office ID first
    roomGroup = document.getElementById(`g${officeId}`);
    
    // If not found by ID, try to find by location (room-X format)
    if (!roomGroup && locationStr) {
      // Extract number from "room-X" format
      const roomMatch = locationStr.match(/room-(\d+)/);
      if (roomMatch && roomMatch[1]) {
        const roomNum = roomMatch[1];
        
        // First, try direct element match with the exact format in your SVG
        roomElement = document.getElementById(`room-${roomNum}-1`);
        if (roomElement) {
          console.log(`Found element directly with ID room-${roomNum}-1 for office ${office.name}`);
          roomGroup = roomElement.closest('g');
        }
        
        // If still not found, try other patterns
        if (!roomGroup) {
          const possibleGroupIds = [
            `g${roomNum}`,              // e.g., g1
            `g${roomNum}-1`,            // e.g., g1-1
            `room-${roomNum}`,          // e.g., room-1
            `room-${roomNum}-1`,        // e.g., room-1-1 (this matches your new SVG structure)
            `room${roomNum}`,           // e.g., room1 
            `room${roomNum}-1`,         // e.g., room1-1
            `g-room-${roomNum}`,        // e.g., g-room-1
          ];
          
          for (const groupId of possibleGroupIds) {
            roomGroup = document.getElementById(groupId);
            if (roomGroup) {
              console.log(`Found group ${groupId} for office ${office.name}`);
              break;
            }
          }
        }
      }
    }
    
    // Try to find a specific room element if group not found by any method
    if (!roomGroup) {
      console.warn(`Room group not found for office ${officeId} with location ${locationStr}`);
      return;
    }
    
    // Find the path or rect element inside the group if we haven't already
    if (!roomElement) {
      roomElement = roomGroup.querySelector('path, rect');
    }
    
    const textEl = roomGroup.querySelector('text');
    
    if (roomElement) {
      roomElement.classList.toggle("room-inactive", !isActive);
      roomElement.classList.add("interactive-room"); // Ensure interactive-room class is added
      roomElement.style.cursor = "pointer";
    }
    if (textEl) textEl.classList.toggle("text-label-inactive", !isActive);
    
    console.log(`Updated appearance for room ${officeId} (${roomGroup.id}), active: ${isActive}`);
  }

  function updateOfficeStatusInDB(officeId, newStatus) {
    console.log(`Updating office status in DB: ${officeId} -> ${newStatus}`);
    fetch("api/update_office_status.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ office_id: officeId, status: newStatus }),
    })
      .then((res) => {
        if (!res.ok) {
          return res.text().then((text) => {
            throw new Error(`Server error ${res.status}: ${text}`);
          });
        }
        return res.json();
      })
      .then((data) => {
        if (!data.success) {
          alert("Failed to update office status.");
          console.error("Server error:", data.message);
        }
      })
      .catch((err) => {
        console.error("Update error:", err);
        alert("An error occurred while updating office status.");
      });
  }

  if (
    !officeDetailsPanel ||
    !panelOfficeName ||
    !officeActiveToggle ||
    !officeStatusText ||
    !closePanelBtn
  ) {
    console.error("Missing one or more essential panel elements.");
    return;
  }

  if (!tooltip) console.warn("Tooltip element not found.");

  if (typeof officesData !== "undefined" && officesData) {
    console.log(`Processing ${officesData.length} offices`);
    
    officesData.forEach((office) => {
      const officeId = office.id.toString();
      officeActiveStates[officeId] = office.status === "active";
    });

    officeActiveToggle.addEventListener("change", function () {
      if (currentSelectedOffice) {
        const idStr = currentSelectedOffice.id.toString();
        const isActive = officeActiveToggle.checked;

        officeActiveStates[idStr] = isActive;
        officeStatusText.textContent = isActive ? "Active" : "Inactive";
        officeStatusText.style.color = isActive ? "#4CAF50" : "#f44336";
        updateRoomAppearanceById(currentSelectedOffice.id, isActive);
        updateOfficeStatusInDB(
          currentSelectedOffice.id,
          isActive ? "active" : "inactive"
        );
      }
    });

    // Map office data to room groups
    officesData.forEach((office) => {
      const id = office.id;
      const idStr = id.toString();
      const officeName = office.name || `Office ${id}`;
      
      // Get location from office data
      const locationStr = office.location || '';
      
      // Find room elements using multiple methods
      let roomGroup = null;
      let roomElement = null;
      
      // Try first by direct group ID match
      roomGroup = document.getElementById(`g${id}`);
      
      // If not found by ID, try to find by location (room-X format)
      if (!roomGroup && locationStr) {
        // Extract number from "room-X" format
        const roomMatch = locationStr.match(/room-(\d+)/);
        if (roomMatch && roomMatch[1]) {
          const roomNum = roomMatch[1];
          
          // First, try direct element match with the exact format in your SVG
          roomElement = document.getElementById(`room-${roomNum}-1`);
          if (roomElement) {
            console.log(`Found element directly with ID room-${roomNum}-1 for office ${officeName}`);
            roomGroup = roomElement.closest('g');
          }
          
          // If still not found, try other patterns
          if (!roomGroup) {      
            // Try various group patterns for the room 
            const possibleGroupIds = [
              `g${roomNum}`,              // e.g., g1
              `g${roomNum}-1`,            // e.g., g1-1
              `room-${roomNum}`,          // e.g., room-1
              `room-${roomNum}-1`,        // e.g., room-1-1 (this matches your new SVG structure)
              `room${roomNum}`,           // e.g., room1 
              `room${roomNum}-1`,         // e.g., room1-1
              `g-room-${roomNum}`,        // e.g., g-room-1
            ];
            
            for (const groupId of possibleGroupIds) {
              roomGroup = document.getElementById(groupId);
              if (roomGroup) {
                console.log(`Found group ${groupId} for office ${officeName}`);
                break;
              }
            }
          }
        }
      }
      
      if (!roomGroup) {
        console.warn(`Room not found for office ${officeName} (ID: ${id}, Location: ${locationStr})`);
        return;
      }
      
      console.log(`Processing room for office ${officeName}, using element: ${roomGroup.id}`);
      
      // Find the path or rect element inside the group if we haven't already
      if (!roomElement) {
        roomElement = roomGroup.querySelector('path, rect');
      }
      
      // Find text element for the label
      const textEl = roomGroup.querySelector('text');
      
      // Log what we found for debugging
      console.log(`  Room element found: ${roomElement ? roomElement.id : 'none'}`);
      console.log(`  Text element found: ${!!textEl}`);
      
      // Set office ID on both group and element
      roomGroup.dataset.officeId = id;
      if (roomElement) {
        roomElement.dataset.officeId = id;
        roomElement.classList.add("interactive-room"); // Add interactive class to element
        roomElement.style.cursor = "pointer";
      }

      // Add click event to both the group and the element
      const handleRoomClick = function(e) {
        // Check if we're in edit mode - if so, don't open the panel
        if (document.body.classList.contains('edit-mode-active')) {
          console.log('In edit mode, not opening panel');
          return;
        }
        
        e.stopPropagation();
        e.preventDefault(); // Prevent any default behavior
        
        currentSelectedOffice = office;
        panelOfficeName.textContent = office.name || "N/A";

        const isActive = officeActiveStates[idStr];
        officeActiveToggle.checked = isActive;
        officeStatusText.textContent = isActive ? "Active" : "Inactive";
        officeStatusText.style.color = isActive ? "#4CAF50" : "#f44336";

        officeDetailsPanel.classList.add("open");
        console.log(`Opening panel for office ${officeName} (click on ${e.currentTarget.tagName} #${e.currentTarget.id})`);
      };

      // Add click handler to both the group and the element
      roomGroup.addEventListener("click", handleRoomClick, true); // Use capture phase
      if (roomElement) {
        roomElement.addEventListener("click", handleRoomClick, true); // Use capture phase
        // Ensure the element is interactive
        roomElement.classList.add("interactive-room");
        roomElement.style.cursor = "pointer";
        roomElement.style.pointerEvents = "auto"; // Ensure click events are captured
      }

      if (textEl) {
        // Apply styling from mobileLabelSetup.js
        textEl.style.fill = "white";
        textEl.style.stroke = "black";
        textEl.style.strokeWidth = "0.5px";
        textEl.style.fontWeight = "bold";
        textEl.style.cursor = "pointer"; // Make text clickable

        // Apply line-breaking logic from mobileLabelSetup.js
        const originalX = textEl.getAttribute("x") || 0;
        const lineHeight = "1.2em"; // Consistent with mobile

        // Clear existing tspans or text content
        textEl.textContent = ""; // Clear direct text content
        while (textEl.firstChild) {
          // Remove existing tspan children if any
          textEl.removeChild(textEl.firstChild);
        }

        if (officeName.includes(" ")) {
          const words = officeName.split(" "); // Split into words
          words.forEach((word, index) => {
            const newTspan = document.createElementNS(
              "http://www.w3.org/2000/svg",
              "tspan"
            );
            newTspan.textContent = word;
            newTspan.setAttribute("x", originalX); // Reset x for each line
            if (index > 0) {
              newTspan.setAttribute("dy", lineHeight); // Apply vertical shift for subsequent lines
            }
            textEl.appendChild(newTspan);
          });
        } else {
          // If no space, use a single tspan
          const newTspan = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "tspan"
          );
          newTspan.textContent = officeName;
          newTspan.setAttribute("x", originalX);
          textEl.appendChild(newTspan);
        }

        // Apply active/inactive class to the textEl itself
        textEl.classList.toggle(
          "text-label-inactive",
          !officeActiveStates[idStr]
        );

        // Add click listener to textEl
        textEl.addEventListener("click", handleRoomClick);

        // Add tooltip to textEl
        if (tooltip) {
          textEl.addEventListener("mousemove", function (e) {
            // Don't show tooltip if in edit mode
            if (document.body.classList.contains('edit-mode-active')) {
              return;
            }
            
            tooltip.innerHTML = officeName;
            tooltip.style.display = "block";
            tooltip.style.left = e.pageX + 15 + "px";
            tooltip.style.top = e.pageY + 15 + "px";
          });
          textEl.addEventListener("mouseout", function () {
            tooltip.style.display = "none";
          });
        }
      }

      if (roomElement) {
        roomElement.classList.add("interactive-room");
        roomElement.style.cursor = "pointer";
        
        // Apply active/inactive styling
        if (!officeActiveStates[idStr]) {
          roomElement.classList.add("room-inactive");
          if (textEl) textEl.classList.add("text-label-inactive");
        }

        if (tooltip) {
          roomElement.addEventListener("mousemove", function (e) {
            // Don't show tooltip if in edit mode
            if (document.body.classList.contains('edit-mode-active')) {
              return;
            }
            
            tooltip.innerHTML = officeName;
            tooltip.style.display = "block";
            tooltip.style.left = e.pageX + 15 + "px";
            tooltip.style.top = e.pageY + 15 + "px";
          });

          roomElement.addEventListener("mouseout", function () {
            tooltip.style.display = "none";
          });
        }
      }
    });
  } else {
    console.error("Offices data (officesData) is missing.");
  }

  closePanelBtn.addEventListener("click", function () {
    officeDetailsPanel.classList.remove("open");
    currentSelectedOffice = null;
    console.log("Panel closed");
  });

  document.getElementById("svg1").addEventListener("click", function (e) {
    if (e.target === this && officeDetailsPanel.classList.contains("open")) {
      officeDetailsPanel.classList.remove("open");
      currentSelectedOffice = null;
      console.log("Panel closed (clicked outside)");
    }
  });
  
  console.log("Label setup complete");
});
