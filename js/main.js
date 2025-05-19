// ======> Login Page Logic <======= //
document.addEventListener("DOMContentLoaded", () => {
    // Initialize any login/register page logic here if needed
  })
  
  // ======> Custom Modal Dialog <======= //
  document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM loaded - initializing modals")
  
    // Create modal elements if they don't exist yet
    if (!document.querySelector(".modal-overlay")) {
      console.log("Creating modal elements")
      const modalOverlay = document.createElement("div")
      modalOverlay.className = "modal-overlay"
      modalOverlay.innerHTML = `
        <div class="modal-container">
          <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Confirmation Required</h3>
            <button class="modal-close">&times;</button>
          </div>
          <div class="modal-body">
            <p id="modal-message">Are you sure you want to proceed?</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" id="modal-cancel">Cancel</button>
            <button class="btn btn-danger" id="modal-confirm">Confirm</button>
          </div>
        </div>
      `
      document.body.appendChild(modalOverlay)
  
      // Close modal when clicking outside
      modalOverlay.addEventListener("click", (e) => {
        if (e.target === modalOverlay) {
          hideModal()
        }
      })
    }
  
    // Set up the modal close button
    const closeBtn = document.querySelector(".modal-close")
    if (closeBtn) {
      closeBtn.addEventListener("click", hideModal)
    }
  
    // Handle all links with data-confirm attribute
    const dataConfirmLinks = document.querySelectorAll("a[data-confirm]")
    console.log("Found data-confirm links:", dataConfirmLinks.length)
  
    dataConfirmLinks.forEach((link) => {
      const confirmMsg = link.getAttribute("data-confirm")
      const href = link.getAttribute("href")
      console.log("Processing data-confirm link with message:", confirmMsg)
  
      // Remove any existing onclick handlers
      link.removeAttribute("onclick")
  
      // Add new click event listener
      link.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()
  
        showModal(confirmMsg, () => {
          window.location.href = href
        })
  
        return false
      })
    })
  
    // Process all links with href attributes that need confirmation
    const allLinks = document.querySelectorAll("a[href]")
    allLinks.forEach((link) => {
      const href = link.getAttribute("href")
      // Check if the link is a delete link (contains delete.php or similar)
      if (href && (href.includes("delete.php") || href.includes("cancel.php") || href.includes("remove"))) {
        // If it doesn't already have a data-confirm attribute, add one
        if (!link.hasAttribute("data-confirm")) {
          link.setAttribute("data-confirm", "Are you sure you want to delete this item?")
  
          // Remove any existing onclick handlers
          link.removeAttribute("onclick")
  
          // Add new click event listener
          link.addEventListener("click", (e) => {
            e.preventDefault()
            e.stopPropagation()
  
            const confirmMsg = link.getAttribute("data-confirm")
            showModal(confirmMsg, () => {
              window.location.href = href
            })
  
            return false
          })
        }
      }
    })
  
    // Apply form styling to all inputs, selects, and textareas
    const formInputs = document.querySelectorAll('input:not([type="submit"]):not([type="button"]):not([type="reset"])')
    const formSelects = document.querySelectorAll("select")
    const formTextareas = document.querySelectorAll("textarea")
  
    formInputs.forEach((input) => {
      if (!input.classList.contains("form-input")) {
        input.classList.add("form-input")
      }
    })
  
    formSelects.forEach((select) => {
      if (!select.classList.contains("form-select")) {
        select.classList.add("form-select")
      }
    })
  
    formTextareas.forEach((textarea) => {
      if (!textarea.classList.contains("form-textarea")) {
        textarea.classList.add("form-textarea")
      }
    })
  
    // Apply button styling to all buttons and submit inputs
    const buttons = document.querySelectorAll('button:not(.btn), input[type="submit"]:not(.btn)')
  
    buttons.forEach((button) => {
      if (!button.classList.contains("btn")) {
        button.classList.add("btn")
  
        // Add primary class if it's a submit button
        if (
          button.type === "submit" &&
          !button.classList.contains("btn-secondary") &&
          !button.classList.contains("btn-danger")
        ) {
          button.classList.add("btn-primary")
        }
      }
    })
  })
  
  // Modal functions - defined in global scope so they can be called from anywhere
  function showModal(message, confirmCallback, cancelCallback) {
    console.log("Showing modal with message:", message)
    const modalOverlay = document.querySelector(".modal-overlay")
    if (!modalOverlay) {
      console.error("Modal overlay not found!")
      return
    }
  
    document.getElementById("modal-message").textContent = message
    modalOverlay.classList.add("active")
  
    const confirmBtn = document.getElementById("modal-confirm")
    const cancelBtn = document.getElementById("modal-cancel")
    const closeBtn = document.querySelector(".modal-close")
  
    // Remove any existing event listeners
    const confirmBtnClone = confirmBtn.cloneNode(true)
    const cancelBtnClone = cancelBtn.cloneNode(true)
    const closeBtnClone = closeBtn.cloneNode(true)
  
    confirmBtn.parentNode.replaceChild(confirmBtnClone, confirmBtn)
    cancelBtn.parentNode.replaceChild(cancelBtnClone, cancelBtn)
    closeBtn.parentNode.replaceChild(closeBtnClone, closeBtn)
  
    // Add new event listeners
    document.getElementById("modal-confirm").addEventListener("click", () => {
      hideModal()
      if (typeof confirmCallback === "function") {
        confirmCallback()
      }
    })
  
    document.getElementById("modal-cancel").addEventListener("click", () => {
      hideModal()
      if (typeof cancelCallback === "function") {
        cancelCallback()
      }
    })
  
    document.querySelector(".modal-close").addEventListener("click", () => {
      hideModal()
      if (typeof cancelCallback === "function") {
        cancelCallback()
      }
    })
  }
  
  function hideModal() {
    console.log("Hiding modal")
    const modalOverlay = document.querySelector(".modal-overlay")
    if (modalOverlay) {
      modalOverlay.classList.remove("active")
    }
  }
  
  // Fix for the onclick confirm issue
  window.addEventListener("DOMContentLoaded", () => {
    // Find all elements with onclick attributes containing "confirm"
    const elementsWithConfirm = document.querySelectorAll('[onclick*="confirm"]')
  
    elementsWithConfirm.forEach((element) => {
      const onclickValue = element.getAttribute("onclick")
  
      // If the onclick contains a return confirm, extract the message
      if (onclickValue && onclickValue.includes("return confirm")) {
        const confirmMessage = onclickValue.match(/confirm$$['"](.+?)['"]$$/)
  
        if (confirmMessage && confirmMessage[1]) {
          const message = confirmMessage[1]
          const href = element.getAttribute("href")
  
          // Remove the original onclick
          element.removeAttribute("onclick")
  
          // Add a new click event listener
          element.addEventListener("click", (e) => {
            e.preventDefault()
  
            // Show our custom modal
            showModal(message, () => {
              // If confirmed, navigate to the href
              if (href) {
                window.location.href = href
              }
            })
  
            return false
          })
        }
      }
    })
  })







