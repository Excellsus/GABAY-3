document.addEventListener("DOMContentLoaded", function () {
  console.log(
    "mobilePanZoomSetup.js: DOM Loaded. Initializing SVG Pan & Zoom..."
  );

  // Select the SVG element within the specific container
  const svgElement = document.querySelector(".floor-plan-container svg#svg1");

  // --- Basic Checks ---
  if (!svgElement) {
    console.error(
      "mobilePanZoomSetup.js: SVG element with selector '.floor-plan-container svg#svg1' not found!"
    );
    return; // Stop script execution if SVG is missing
  } else {
    console.log("mobilePanZoomSetup.js: SVG container found.");
  }
  // --- End Basic Checks ---

  // --- Initialize svg-pan-zoom ---
  if (typeof svgPanZoom === "function") {
    console.log("Initializing svg-pan-zoom...");
    try {
      const panZoomInstance = svgPanZoom(svgElement, {
        // Use the element directly
        zoomEnabled: true, // Enable zooming
        controlIconsEnabled: false, // Show +/- zoom buttons
        controlIconsEnabled: true, // Show +/- zoom buttons
        fit: true, // Fit SVG to container on load
        fit: false, // Disable auto-fit on load
        center: true, // Center SVG in container on load

        // preventMouseEventsDefault: true, // Might be needed if clicks don't register reliably
      }); // Pass the element, not the selector string
      console.log("svg-pan-zoom initialized successfully.");

      // Make the pan/zoom instance responsive to window resizing
      window.addEventListener("resize", () => {
        panZoomInstance.resize(); // Recalculate SVG size
        panZoomInstance.fit(); // Re-fit SVG to the new container size
        panZoomInstance.center(); // Re-center SVG
      });
    } catch (e) {
      console.error("Error initializing svg-pan-zoom:", e);
    }
  } else {
    console.warn(
      "svg-pan-zoom library not found or loaded. Make sure the script tag for svg-pan-zoom.min.js is included BEFORE this file in the HTML."
    );
  }

  console.log(
    "mobilePanZoomSetup.js: SVG Pan & Zoom script finished initialization."
  );
});
