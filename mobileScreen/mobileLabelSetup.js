document.addEventListener("DOMContentLoaded", function () {
  console.log("mobileLabelSetup.js loaded and DOM ready.");

  const tooltip = document.getElementById("floorplan-tooltip");

  if (!tooltip) {
    console.error(
      "Tooltip element (#floorplan-tooltip) not found! Make sure it exists in your floorPlan.php HTML."
    );
    // Decide if you want to stop execution if tooltip is missing, e.g., return;
  }

  // Check if officesData is available (passed from PHP)
  if (typeof officesData !== "undefined" && officesData) {
    console.log("Processing office data for SVG linking (Mobile)...");

    // Find all path elements whose ID starts with "room-"
    const roomElements = document.querySelectorAll('path[id^="room-"]');
    console.log(
      `Found ${roomElements.length} potential room elements (Mobile).`
    );

    roomElements.forEach((roomElement) => {
      const svgId = roomElement.id;
      let officeId = null;

      // Extract the number part from the ID (e.g., "room-5" -> 5)
      try {
        const idParts = svgId.split("-");
        if (idParts.length === 2 && !isNaN(parseInt(idParts[1], 10))) {
          officeId = parseInt(idParts[1], 10);
        }
      } catch (e) {
        /* Ignore if ID format is unexpected */
      }

      // DEBUG: Check if the element was found
      // console.log(`Processing SVG element #${svgId}:`, roomElement); // Can be noisy

      // Find the office data from the array passed by PHP
      const officeInfo = officesData.find(
        (office) => parseInt(office.id, 10) === officeId
      );

      if (officeId !== null && officeInfo) {
        // Check if we found both the element and data
        console.log(
          `Linking SVG element #${svgId} to Office ID ${officeId} (${officeInfo.name})`
        );

        // Add a class for  styling/identification (optional)
        roomElement.classList.add("interactive-room");
        // Store office ID for easy retrieval (optional)
        roomElement.dataset.officeId = officeId;

        // --- Define the action to perform on click/touch ---
        const handleRoomInteraction = function (event) {
          // Prevent default behavior, especially for touchend,
          // to avoid potential duplicate actions (like zooming or firing a click later)
          // event.preventDefault(); // Removed to allow default pan/zoom behavior
          event.stopPropagation(); // Prevent click from bubbling up to the SVG background
          console.log(
            `Interaction detected on ${svgId} via ${event.type}, stopping propagation.`
          );

          // --- Update Drawer Content ---
          // Get references to the elements inside the drawer
          const drawerName = document.getElementById("drawer-office-name");
          const drawerDetails = document.getElementById(
            "drawer-office-details"
          );
          const drawerContact = document.getElementById(
            "drawer-office-contact"
          );
          const drawerLocationDetail = document.getElementById(
            "drawer-office-location-detail"
          );

          // Update the text content of the drawer elements
          if (drawerName) {
            drawerName.textContent = officeInfo.name || "N/A";
          }
          if (drawerDetails) {
            drawerDetails.textContent = `Details: ${
              officeInfo.details || "N/A"
            }`;
          }
          if (drawerContact) {
            drawerContact.textContent = `Contact: ${
              officeInfo.contact || "N/A"
            }`;
          }
          if (drawerLocationDetail) {
            // Assuming officeInfo has a 'location' property
            drawerLocationDetail.textContent = `Location: ${
              officeInfo.location || "N/A"
            }`;
          }
          // --- End Update Drawer Content ---

          // --- Open the Drawer ---
          // Check if the openDrawer function (defined in explore.php) exists and call it.
          // This ensures the drawer opens or stays open without toggling.
          if (typeof window.openDrawer === "function") {
            console.log(
              "Calling window.openDrawer() to ensure drawer is open."
            );
            window.openDrawer();
          } else {
            console.error(
              "window.openDrawer function not found! Make sure it's defined in explore.php."
            );
          }
          // --- End Open the Drawer ---

          // You could add alternative actions here if needed,
          // like logging the clicked office info:
          // console.log("Clicked Office Info:", officeInfo);
        };

        // --- Add Listeners ---
        // For mouse clicks
        roomElement.addEventListener("click", handleRoomInteraction);
        // For touch taps (end of touch)
        roomElement.addEventListener("touchend", handleRoomInteraction);

        // --- Find and add listeners to the corresponding text label ---
        const parentGroup = roomElement.parentElement; // Assumes path and text share a parent <g>
        let textElement = null;
        if (parentGroup) {
          textElement = parentGroup.querySelector("text"); // Find <text> within the group
        }

        if (textElement) {
          console.log(`Found text element for ${svgId}:`, textElement);

          // --- Apply Styling: White text with black stroke ---
          textElement.style.fill = "white"; // Set font color to white
          textElement.style.stroke = "black"; // Set outline color to black
          textElement.style.strokeWidth = "0.5px"; // Set outline thickness (adjust as needed)
          textElement.style.fontWeight = "bold"; // Make the text bold
          // --- End Styling ---

          // --- Update Text Content with Line Breaking ---
          const newText = officeInfo.name || svgId; // Use office name, fallback to original ID
          const originalX = textElement.getAttribute("x") || 0;
          const lineHeight = "1.2em"; // Adjust line height as needed

          // Clear existing tspans or text content
          textElement.textContent = ""; // Clear direct text content
          while (textElement.firstChild) {
            // Remove existing tspan children if any
            textElement.removeChild(textElement.firstChild);
          }

          // Check if the name contains a space and needs splitting
          if (newText.includes(" ")) {
            const words = newText.split(" "); // Split into words
            words.forEach((word, index) => {
              const tspan = document.createElementNS(
                "http://www.w3.org/2000/svg",
                "tspan"
              );
              tspan.textContent = word;
              tspan.setAttribute("x", originalX); // Reset x for each line
              if (index > 0) {
                tspan.setAttribute("dy", lineHeight); // Apply vertical shift for subsequent lines
              } else {
                // For the first line, dy is relative to the text element's y, setting explicitly might be needed if y isn't set
                // Or rely on default positioning for the first tspan.
                // If text isn't aligning correctly, you might need to set dy="0" or adjust the text element's y attribute initially.
              }
              textElement.appendChild(tspan);
            });
            console.log(
              `Split label for ${svgId} into lines: ${words.join(", ")}`
            );
          } else {
            // If no space, just set the text content directly (or use a single tspan)
            textElement.textContent = newText;
            console.log(
              `Updated label for ${svgId} to: ${newText} (single line)`
            );
          }
          // --- End Update Text Content ---

          // Add the same listeners to the text element
          textElement.addEventListener("click", handleRoomInteraction);
          textElement.addEventListener("touchend", handleRoomInteraction);

          // Stroke removed for cleaner text appearance
          // textElement.style.cursor = "pointer"; // Removed cursor style setting
        } else {
          console.warn(`Could not find associated <text> element for ${svgId}`);
        }
        // --- End text label listener setup ---

        // --- Add Tooltip Listeners ---
        if (tooltip) {
          roomElement.addEventListener("mousemove", function (event) {
            tooltip.innerHTML = officeInfo.name; // Set tooltip text
            tooltip.style.display = "block"; // Make it visible
            // Position near cursor (adjust offsets as needed)
            // Using pageX/pageY for position relative to the whole document
            tooltip.style.left = event.pageX + 15 + "px"; // Offset slightly right
            tooltip.style.top = event.pageY + 15 + "px"; // Offset slightly down
          });

          roomElement.addEventListener("mouseout", function () {
            tooltip.style.display = "none"; // Hide tooltip
          });
        }
        // --- End Tooltip Listeners ---
      } else {
        // More specific warning
        if (officeId === null) {
          console.warn(
            `Could not extract valid ID from SVG element: #${svgId} (Mobile)`
          );
        } else if (!officeInfo) {
          // This might happen if an SVG room exists but has no matching DB entry
          // console.warn(`Could not find office data for ID: ${officeId} in officesData (Mobile).`);
        }
      }
    });
  } else {
    console.error(
      "Offices data (officesData variable) is not available in mobileLabelSetup.js."
    );
  }

  // --- Add listener to SVG background to close drawer ---
  const svgContainer = document.querySelector(".floor-plan-container svg#svg1");
  if (svgContainer) {
    svgContainer.addEventListener("click", function (event) {
      // Check if the click was directly on the SVG background itself,
      // not on a child element like a path or text (which should have stopped propagation).
      if (event.target === svgContainer) {
        console.log("SVG background clicked. Closing drawer.");
        if (typeof window.closeDrawer === "function") {
          window.closeDrawer();
        } else {
          console.error("window.closeDrawer function not found!");
        }
      }
    });
  } else {
    console.error(
      "SVG container '.floor-plan-container svg#svg1' not found for background click listener."
    );
  }
});
