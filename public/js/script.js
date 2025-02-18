document.addEventListener("DOMContentLoaded", function () {
    // Gestion des carousels
    const carousels = document.querySelectorAll(".carousel");

    carousels.forEach(carousel => {
        const leftBtn = carousel.parentElement.querySelector(".left-btn");
        const rightBtn = carousel.parentElement.querySelector(".right-btn");

        if (carousel && leftBtn && rightBtn) {
            leftBtn.addEventListener("click", () => {
                carousel.scrollBy({ left: -300, behavior: "smooth" });
            });

            rightBtn.addEventListener("click", () => {
                carousel.scrollBy({ left: 300, behavior: "smooth" });
            });
        }
    });

    // Gestion des témoignages avec pagination
    const testimonialItems = document.querySelectorAll(".testimonial-item");
    const dots = document.querySelectorAll(".dot");
    const itemsPerPage = 3;
    let currentPage = 0;

    function showTestimonials(page) {
        const startIndex = page * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        testimonialItems.forEach((item, index) => {
            item.classList.toggle("active", index >= startIndex && index < endIndex);
        });

        dots.forEach((dot, index) => {
            dot.classList.toggle("active", index === page);
        });
    }

    dots.forEach(dot => {
        dot.addEventListener("click", function () {
            currentPage = parseInt(this.getAttribute("data-index"));
            showTestimonials(currentPage);
        });
    });

    showTestimonials(currentPage);

   }
    
);