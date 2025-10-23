// Mobile Menu Toggle
document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 Script loaded successfully")

  // Setup mobile menu
  setupMobileMenu()

  // Setup scroll buttons
  setupScrollButtons()

  // Contact form
  setupContactForm()

  // Smooth scrolling for anchor links
  setupSmoothScrolling()

  // Header scroll effect
  setupHeaderScrollEffect()

  // Setup touch/swipe support
  setupTouchSupport()
})

// Mobile Menu Setup - FIXED VERSION
function setupMobileMenu() {
  const mobileMenuBtn = document.getElementById("mobileMenuBtn")
  const mobileNav = document.getElementById("mobileNav")

  console.log("Mobile menu elements:", {
    button: !!mobileMenuBtn,
    nav: !!mobileNav,
  })

  if (mobileMenuBtn && mobileNav) {
    const mobileMenuIcon = mobileMenuBtn.querySelector("i")

    mobileMenuBtn.addEventListener("click", (e) => {
      e.preventDefault()
      e.stopPropagation()

      console.log("📱 Mobile menu button clicked")

      const isActive = mobileNav.classList.contains("active")
      console.log("Current state:", isActive ? "open" : "closed")

      if (isActive) {
        // Close menu
        mobileNav.classList.remove("active")
        if (mobileMenuIcon) {
          mobileMenuIcon.classList.remove("fa-times")
          mobileMenuIcon.classList.add("fa-bars")
        }
        document.body.style.overflow = "auto"
        console.log("❌ Menu closed")
      } else {
        // Open menu
        mobileNav.classList.add("active")
        if (mobileMenuIcon) {
          mobileMenuIcon.classList.remove("fa-bars")
          mobileMenuIcon.classList.add("fa-times")
        }
        document.body.style.overflow = "hidden"
        console.log("✅ Menu opened")
      }
    })

    // Close mobile menu when clicking on links
    const mobileLinks = mobileNav.querySelectorAll("a")
    mobileLinks.forEach((link) => {
      link.addEventListener("click", () => {
        console.log("🔗 Mobile link clicked, closing menu")
        mobileNav.classList.remove("active")
        if (mobileMenuIcon) {
          mobileMenuIcon.classList.remove("fa-times")
          mobileMenuIcon.classList.add("fa-bars")
        }
        document.body.style.overflow = "auto"
      })
    })

    // Close menu when clicking outside
    mobileNav.addEventListener("click", (e) => {
      if (e.target === mobileNav) {
        console.log("🔗 Clicked outside menu, closing")
        mobileNav.classList.remove("active")
        if (mobileMenuIcon) {
          mobileMenuIcon.classList.remove("fa-times")
          mobileMenuIcon.classList.add("fa-bars")
        }
        document.body.style.overflow = "auto"
      }
    })

    console.log("✅ Mobile menu setup complete")
  } else {
    console.error("❌ Mobile menu elements not found!")
  }
}

// Setup Scroll Buttons - IMPROVED VERSION
function setupScrollButtons() {
  const kayuScrollContainer = document.getElementById("kayuProductsScroll")
  const kayuLeftBtn = document.getElementById("kayuScrollLeft")
  const kayuRightBtn = document.getElementById("kayuScrollRight")

  const minyakScrollContainer = document.getElementById("minyakProductsScroll")
  const minyakLeftBtn = document.getElementById("minyakScrollLeft")
  const minyakRightBtn = document.getElementById("minyakScrollRight")

  // Kayu Gaharu scroll buttons
  if (kayuLeftBtn && kayuRightBtn && kayuScrollContainer) {
    kayuLeftBtn.addEventListener("click", () => {
      kayuScrollContainer.scrollBy({ left: -300, behavior: "smooth" })
    })

    kayuRightBtn.addEventListener("click", () => {
      kayuScrollContainer.scrollBy({ left: 300, behavior: "smooth" })
    })

    function updateKayuScrollButtons() {
      const { scrollLeft, scrollWidth, clientWidth } = kayuScrollContainer
      const maxScroll = scrollWidth - clientWidth

      // Update button opacity based on scroll position
      kayuLeftBtn.style.opacity = scrollLeft <= 0 ? "0.5" : "1"
      kayuRightBtn.style.opacity = scrollLeft >= maxScroll - 1 ? "0.5" : "1"

      // Disable buttons at extremes
      kayuLeftBtn.disabled = scrollLeft <= 0
      kayuRightBtn.disabled = scrollLeft >= maxScroll - 1
    }

    kayuScrollContainer.addEventListener("scroll", updateKayuScrollButtons)
    // Initial call to set correct state
    setTimeout(updateKayuScrollButtons, 100)
  }

  // Minyak Gaharu scroll buttons
  if (minyakLeftBtn && minyakRightBtn && minyakScrollContainer) {
    minyakLeftBtn.addEventListener("click", () => {
      minyakScrollContainer.scrollBy({ left: -300, behavior: "smooth" })
    })

    minyakRightBtn.addEventListener("click", () => {
      minyakScrollContainer.scrollBy({ left: 300, behavior: "smooth" })
    })

    function updateMinyakScrollButtons() {
      const { scrollLeft, scrollWidth, clientWidth } = minyakScrollContainer
      const maxScroll = scrollWidth - clientWidth

      // Update button opacity based on scroll position
      minyakLeftBtn.style.opacity = scrollLeft <= 0 ? "0.5" : "1"
      minyakRightBtn.style.opacity = scrollLeft >= maxScroll - 1 ? "0.5" : "1"

      // Disable buttons at extremes
      minyakLeftBtn.disabled = scrollLeft <= 0
      minyakRightBtn.disabled = scrollLeft >= maxScroll - 1
    }

    minyakScrollContainer.addEventListener("scroll", updateMinyakScrollButtons)
    // Initial call to set correct state
    setTimeout(updateMinyakScrollButtons, 100)
  }
}

// Contact Form Setup - ENHANCED VERSION WITH NOTIFICATIONS
function setupContactForm() {
  const contactForm = document.getElementById("contactForm")

  if (contactForm) {
    console.log("✅ Contact form found and event listener attached")

    contactForm.addEventListener("submit", async (e) => {
      e.preventDefault()
      console.log("🚀 Form submission started")

      // Show loading state
      const submitBtn = contactForm.querySelector('button[type="submit"]')
      const originalText = submitBtn.textContent
      submitBtn.classList.add("btn-loading")
      submitBtn.textContent = "Mengirim..."
      submitBtn.disabled = true

      // Clear previous messages
      clearMessages()

      try {
        const formData = new FormData(contactForm)

        // Client-side validation
        const name = formData.get("name")?.trim() || ""
        const email = formData.get("email")?.trim() || ""
        const phone = formData.get("phone")?.trim() || ""
        const message = formData.get("message")?.trim() || ""

        // Validation with specific error messages
        if (!name) {
          showMessage("error", "Isi form nama")
          return
        }

        if (!email) {
          showMessage("error", "Isi form email")
          return
        }

        if (!isValidEmail(email)) {
          showMessage("error", "Format email tidak valid")
          return
        }

        if (!message) {
          showMessage("error", "Isi form message")
          return
        }

        if (message.length < 10) {
          showMessage("error", "Karakter message kurang dari 10")
          return
        }

        console.log("📤 Sending request to prosesscontact.php")

        const response = await fetch("prosesscontact.php", {
          method: "POST",
          body: formData,
        })

        console.log("📥 Response received:", {
          status: response.status,
          statusText: response.statusText,
        })

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`)
        }

        const contentType = response.headers.get("content-type")
        if (!contentType?.includes("application/json")) {
          const text = await response.text()
          console.error("❌ Non-JSON response received:", text)
          throw new Error("Server returned non-JSON response")
        }

        const data = await response.json()
        console.log("✅ JSON response:", data)

        if (data.success) {
          showMessage("success", "Pesan berhasil terkirim")
          contactForm.reset()
          console.log("🎉 Form submitted successfully!")
        } else {
          // Handle specific error messages from server
          if (data.message.includes("pesan yang sama sudah dikirim")) {
            showMessage("error", "Pesan yang sama sudah terkirim coba lagi dalam 5 menit")
          } else {
            showMessage("error", data.message)
          }
          console.log("❌ Form submission failed:", data.message)
        }
      } catch (error) {
        console.error("💥 Error during form submission:", error)

        let errorMessage = "Terjadi kesalahan saat mengirim pesan."

        if (error.message.includes("Failed to fetch")) {
          errorMessage = "Tidak dapat terhubung ke server. Periksa koneksi internet Anda."
        } else if (error.message.includes("HTTP")) {
          errorMessage = `Server error: ${error.message}`
        } else if (error.message.includes("non-JSON")) {
          errorMessage = "Server configuration error. Hubungi administrator."
        }

        showMessage("error", errorMessage)
      } finally {
        // Reset button state
        submitBtn.classList.remove("btn-loading")
        submitBtn.textContent = originalText
        submitBtn.disabled = false
      }
    })
  } else {
    console.error("❌ Contact form not found!")
  }
}

// Helper functions for contact form
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

function showMessage(type, message) {
  clearMessages()

  const messageDiv = document.createElement("div")
  messageDiv.className = `form-message form-message-${type}`

  if (type === "success") {
    messageDiv.classList.add("auto-hide")
  }

  messageDiv.innerHTML = `
    <div class="message-content">
      <i class="fas ${type === "success" ? "fa-check-circle" : "fa-exclamation-circle"}"></i>
      <span>${message}</span>
      <button class="close-btn" onclick="this.parentElement.parentElement.remove()">
        <i class="fas fa-times"></i>
      </button>
    </div>
  `

  // Cari container khusus untuk message atau fallback ke form
  const messageContainer = document.getElementById("messageContainer")
  const contactForm = document.getElementById("contactForm")

  if (messageContainer) {
    // Gunakan container khusus jika ada
    messageContainer.appendChild(messageDiv)
  } else if (contactForm) {
    // Fallback: masukkan sebelum form
    const formCard = contactForm.closest(".contact-form-card")
    if (formCard) {
      formCard.insertBefore(messageDiv, contactForm)
    } else {
      contactForm.parentNode.insertBefore(messageDiv, contactForm)
    }
  }

  // Auto-hide success messages after 5 seconds
  if (type === "success") {
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.style.animation = "fadeOut 0.5s ease-in forwards"
        setTimeout(() => {
          if (messageDiv.parentNode) {
            messageDiv.remove()
          }
        }, 500)
      }
    }, 5000)
  }
}

function clearMessages() {
  // Clear messages from both possible locations
  const messageContainer = document.getElementById("messageContainer")
  if (messageContainer) {
    messageContainer.innerHTML = ""
  }
  document.querySelectorAll(".form-message").forEach((msg) => msg.remove())
}

// Smooth scrolling for anchor links
function setupSmoothScrolling() {
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })
}

// Header scroll effect
function setupHeaderScrollEffect() {
  const header = document.querySelector(".header")
  if (header) {
    window.addEventListener("scroll", () => {
      if (window.scrollY > 100) {
        header.classList.add("scrolled")
      } else {
        header.classList.remove("scrolled")
      }
    })
  }
}

// Touch/swipe support for mobile
function setupTouchSupport() {
  let startX = 0
  let scrollLeft = 0

  const scrollContainers = document.querySelectorAll(".products-scroll")

  scrollContainers.forEach((container) => {
    // Mouse events for desktop
    container.addEventListener("mousedown", (e) => {
      container.classList.add("dragging")
      startX = e.pageX - container.offsetLeft
      scrollLeft = container.scrollLeft
      container.style.cursor = "grabbing"
    })

    container.addEventListener("mouseleave", () => {
      container.classList.remove("dragging")
      container.style.cursor = "grab"
    })

    container.addEventListener("mouseup", () => {
      container.classList.remove("dragging")
      container.style.cursor = "grab"
    })

    container.addEventListener("mousemove", (e) => {
      if (!container.classList.contains("dragging")) return
      e.preventDefault()
      const x = e.pageX - container.offsetLeft
      const walk = (x - startX) * 2
      container.scrollLeft = scrollLeft - walk
    })

    // Touch events for mobile
    container.addEventListener("touchstart", (e) => {
      startX = e.touches[0].pageX - container.offsetLeft
      scrollLeft = container.scrollLeft
    })

    container.addEventListener("touchmove", (e) => {
      if (e.touches.length > 1) return // Ignore multi-touch
      const x = e.touches[0].pageX - container.offsetLeft
      const walk = (x - startX) * 2
      container.scrollLeft = scrollLeft - walk
    })

    // Set initial cursor
    container.style.cursor = "grab"
  })
}

// Function for changing main image in product detail pages
function changeMainImage(imageSrc, thumbnail) {
  const mainImg = document.getElementById("mainImg")
  if (mainImg) {
    mainImg.src = imageSrc
  }

  // Update active thumbnail
  document.querySelectorAll(".thumbnail").forEach((thumb) => {
    thumb.classList.remove("active")
  })
  if (thumbnail) {
    thumbnail.classList.add("active")
  }
}
