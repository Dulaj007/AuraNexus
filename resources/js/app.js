import './bootstrap';
    document.addEventListener("DOMContentLoaded", () => {
            // Dropdown toggles
            document.querySelectorAll(".category-toggle").forEach(btn => {
                btn.addEventListener("click", () => {
                    const forums = btn.parentElement.querySelector(".forums");
                    const arrow = btn.querySelector(".arrow");
                    if (forums.style.maxHeight) {
                        forums.style.maxHeight = null;
                        arrow.style.transform = "rotate(0deg)";
                    } else {
                        forums.style.maxHeight = forums.scrollHeight + "px";
                        arrow.style.transform = "rotate(180deg)";
                    }
                });
            });

            // Hover highlight for all sidebar instances
            document.querySelectorAll(".sidebarMenu").forEach(sidebar => {
                const highlight = sidebar.querySelector(".sidebarHighlight");
                const items = sidebar.querySelectorAll("[data-sidebar-item]");
                items.forEach(item => {
                    item.addEventListener("mouseenter", () => {
                        const rect = item.getBoundingClientRect();
                        const parentRect = sidebar.getBoundingClientRect();
                        highlight.style.width = rect.width + "px";
                        highlight.style.height = rect.height + "px";
                        highlight.style.transform = `translate(${rect.left - parentRect.left}px, ${rect.top - parentRect.top}px)`;
                    });
                });
                sidebar.addEventListener("mouseleave", () => {
                    highlight.style.width = "0";
                    highlight.style.height = "0";
                });
            });

            // Desktop Sidebar Toggle
            const sidebar = document.getElementById("sidebar");
            const sidebarInner = document.getElementById("sidebarInner");
            const toggle = document.getElementById("sidebarToggle");
            const toggleIcon = document.getElementById("toggleIcon");
            const mainContent = document.getElementById("mainContent");
            if(toggle) {
                toggle.addEventListener("click", () => {
                    const isCollapsed = sidebar.classList.contains("w-16");
                    if (!isCollapsed) {
                           sidebar.classList.replace("w-64", "w-16");
            mainContent.classList.replace("2xl:ml-64", "2xl:ml-16");
                                sidebarInner.classList.add("opacity-0", "pointer-events-none");
                                toggle.style.left = "48px";
                                toggleIcon.style.transform = "rotate(180deg)";
                            } else {
                                sidebar.classList.replace("w-16", "w-64");
            mainContent.classList.replace("2xl:ml-16", "2xl:ml-64");
                                sidebarInner.classList.remove("opacity-0", "pointer-events-none");
                                toggle.style.left = "242px";
                                toggleIcon.style.transform = "rotate(0deg)";
                            }
                        });
                    }

            // Mobile Menu
            const mobileBtn = document.getElementById("mobileMenuBtn");
            const mobileMenu = document.getElementById("mobileMenu");
            const mobileDrawer = document.getElementById("mobileDrawer");
            const mobileOverlay = document.getElementById("mobileOverlay");
            const closeMobile = document.getElementById("closeMobileMenu");

            function openMobile() {
                mobileMenu.classList.remove("hidden");
                setTimeout(() => {
                    mobileDrawer.classList.replace("translate-x-[-100%]", "translate-x-0");
                    mobileOverlay.classList.replace("opacity-0", "opacity-100");
                }, 10);
            }

            function closeMobileFn() {
                mobileDrawer.classList.replace("translate-x-0", "translate-x-[-100%]");
                mobileOverlay.classList.replace("opacity-100", "opacity-0");
                setTimeout(() => mobileMenu.classList.add("hidden"), 300);
            }

            if(mobileBtn) mobileBtn.addEventListener("click", openMobile);
            if(closeMobile) closeMobile.addEventListener("click", closeMobileFn);
            if(mobileOverlay) mobileOverlay.addEventListener("click", closeMobileFn);
        });