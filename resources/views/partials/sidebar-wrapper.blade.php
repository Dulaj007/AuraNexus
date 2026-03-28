{{-- Desktop Sidebar --}}
<aside id="sidebar"
       class="hidden 2xl:flex fixed top-0 h-screen w-64 border-r border-[var(--an-border)] bg-[color:var(--an-bg)]/20 backdrop-blur-md overflow-y-auto custom-scrollbar">
    <div id="sidebarInner" class="transition-opacity duration-300 pt-25 ">
        @include('partials.sidebar')
    </div>
</aside>

{{-- Sidebar Collapse Toggle (Desktop) --}}
<button id="sidebarToggle" class="hidden 2xl:flex fixed top-25 left-[242px] z-40 ...">
    <svg id="toggleIcon" ...></svg>
</button>

{{-- Mobile Drawer --}}
<div id="mobileMenu" class="fixed inset-0 z-[60] hidden">
    <div id="mobileOverlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
    <div id="mobileDrawer" class="absolute left-0 top-0 bottom-0 bg-[color:var(--an-bg)]/60 backdrop-blur-md ...">
        <div class="p-4 border-b border-[var(--an-border)] flex items-center justify-between">
            <span class="font-bold text-lg">{{ $appName }}</span>
            <button id="closeMobileMenu" ...>X</button>
        </div>
        <div class="flex-1 space-y-1 overflow-y-auto custom-scrollbar">
            @include('partials.sidebar')
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
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
                sidebarInner.classList.add("opacity-0","pointer-events-none");
                toggle.style.left = "48px";
                toggleIcon.style.transform = "rotate(180deg)";
            } else {
                sidebar.classList.replace("w-16","w-64");
                mainContent.classList.replace("2xl:ml-16","2xl:ml-64");
                sidebarInner.classList.remove("opacity-0","pointer-events-none");
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
            mobileDrawer.classList.replace("translate-x-[-100%]","translate-x-0");
            mobileOverlay.classList.replace("opacity-0","opacity-100");
        },10);
    }
    function closeMobileFn() {
        mobileDrawer.classList.replace("translate-x-0","translate-x-[-100%]");
        mobileOverlay.classList.replace("opacity-100","opacity-0");
        setTimeout(()=>mobileMenu.classList.add("hidden"),300);
    }
    if(mobileBtn) mobileBtn.addEventListener("click", openMobile);
    if(closeMobile) closeMobile.addEventListener("click", closeMobileFn);
    if(mobileOverlay) mobileOverlay.addEventListener("click", closeMobileFn);
});
</script>

<style>
.sidebarHighlight {
    transition: transform 0.25s ease, width 0.25s ease, height 0.25s ease;
    background-color: var(--an-card-2);
    position: absolute;
    z-index: 0;
    border-radius: 0.75rem;
}
#sidebar { box-sizing: border-box; padding-top: 0; }
.sidebarMenu { padding-top: 0; }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
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
                sidebarInner.classList.add("opacity-0","pointer-events-none");
                toggle.style.left = "48px";
                toggleIcon.style.transform = "rotate(180deg)";
            } else {
                sidebar.classList.replace("w-16","w-64");
                mainContent.classList.replace("2xl:ml-16","2xl:ml-64");
                sidebarInner.classList.remove("opacity-0","pointer-events-none");
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
            mobileDrawer.classList.replace("translate-x-[-100%]","translate-x-0");
            mobileOverlay.classList.replace("opacity-0","opacity-100");
        },10);
    }
    function closeMobileFn() {
        mobileDrawer.classList.replace("translate-x-0","translate-x-[-100%]");
        mobileOverlay.classList.replace("opacity-100","opacity-0");
        setTimeout(()=>mobileMenu.classList.add("hidden"),300);
    }
    if(mobileBtn) mobileBtn.addEventListener("click", openMobile);
    if(closeMobile) closeMobile.addEventListener("click", closeMobileFn);
    if(mobileOverlay) mobileOverlay.addEventListener("click", closeMobileFn);
});
</script>

<style>
.sidebarHighlight {
    transition: transform 0.25s ease, width 0.25s ease, height 0.25s ease;
    background-color: var(--an-card-2);
    position: absolute;
    z-index: 0;
    border-radius: 0.75rem;
}
#sidebar { box-sizing: border-box; padding-top: 0; }
.sidebarMenu { padding-top: 0; }
</style>