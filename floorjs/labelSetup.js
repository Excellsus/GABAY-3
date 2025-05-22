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

  function updateRoomAppearanceById(officeId, isActive) {
    const roomEl = document.getElementById(`room-${officeId}`);
    const textEl = document.getElementById(`text${officeId}`);

    if (roomEl) roomEl.classList.toggle("room-inactive", !isActive);
    if (textEl) textEl.classList.toggle("text-label-inactive", !isActive);
  }

  function updateOfficeStatusInDB(officeId, newStatus) {
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

    officesData.forEach((office) => {
      const id = office.id;
      const idStr = id.toString();
      const officeName = office.name || `Office ${id}`;
      const room = document.getElementById(`room-${id}`);
      const textEl = document.getElementById(`text${id}`); // Target the <text> element

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

        // Add click listener to textEl to open the panel
        textEl.addEventListener("click", function (e) {
          e.stopPropagation();
          currentSelectedOffice = office;
          panelOfficeName.textContent = office.name || "N/A";

          const isActive = officeActiveStates[idStr];
          officeActiveToggle.checked = isActive;
          officeStatusText.textContent = isActive ? "Active" : "Inactive";
          officeStatusText.style.color = isActive ? "#4CAF50" : "#f44336";

          officeDetailsPanel.classList.add("open");
        });

        // Add tooltip to textEl
        if (tooltip) {
          textEl.addEventListener("mousemove", function (e) {
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

      if (room) {
        room.classList.add("interactive-room");
        room.dataset.officeId = id;
        room.style.cursor = "pointer";
        updateRoomAppearanceById(id, officeActiveStates[idStr]);

        room.addEventListener("click", function (e) {
          e.stopPropagation();
          currentSelectedOffice = office;
          panelOfficeName.textContent = office.name || "N/A";

          const isActive = officeActiveStates[idStr];
          officeActiveToggle.checked = isActive;
          officeStatusText.textContent = isActive ? "Active" : "Inactive";
          officeStatusText.style.color = isActive ? "#4CAF50" : "#f44336";

          officeDetailsPanel.classList.add("open");
        });

        if (tooltip) {
          room.addEventListener("mousemove", function (e) {
            tooltip.innerHTML = officeName;
            tooltip.style.display = "block";
            tooltip.style.left = e.pageX + 15 + "px";
            tooltip.style.top = e.pageY + 15 + "px";
          });

          room.addEventListener("mouseout", function () {
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
  });

  document.getElementById("svg1").addEventListener("click", function (e) {
    if (e.target === this && officeDetailsPanel.classList.contains("open")) {
      officeDetailsPanel.classList.remove("open");
      currentSelectedOffice = null;
    }
  });
});
