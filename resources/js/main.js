require('./http/apiClient');

/*******************************************************
                Accordion Sidebar Menu Start
*******************************************************/
document.addEventListener('DOMContentLoaded', function () {
    var accItem = document.getElementsByClassName('accordionItem');
    var accHD = document.getElementsByClassName('accordionItemHeading');

    for (var i = 0; i < accHD.length; i++) {
        accHD[i].addEventListener('click', toggleItem, false);
    }

    function toggleItem(e) {
        // Prevent default anchor behavior
        if (e) e.preventDefault();

        var parent = this.parentNode;
        // Check if it's currently closed by checking the class list
        var isClosed = parent.classList.contains('closeIt');

        // Close all items
        for (var i = 0; i < accItem.length; i++) {
            accItem[i].classList.remove('openIt');
            accItem[i].classList.add('closeIt');
        }

        // If it was closed, open it
        if (isClosed) {
            parent.classList.remove('closeIt');
            parent.classList.add('openIt');
        }
    }
});
/*******************************************************
                Accordion Sidebar Menu End
*******************************************************/

/*******************************************************
            Toggle The Side Navigation Start
*******************************************************/
// document.getElementById("sidebarToggle").addEventListener("click", toggleSidebar);

// var ts = document.getElementById('sidebarToggle');
// if (ts) {
//    ts.addEventListener("click", toggleSidebar);
// }

function toggleSidebar() {
    let toggle = document.querySelector('body');
    toggle.classList.toggle('sidebar-toggled');
}

window.addEventListener("resize", resiz);
function resiz() {
    if (screen.width < 769) {
        var element = document.querySelector("body");
        element.classList.remove("sidebar-toggled");
    }
}
/*******************************************************
            Toggle The Side Navigation End
*******************************************************/

/*******************************************************
               Header More Filter Start
*******************************************************/
function openMoreFilter() {
    var omf = document.getElementById("more_filter");
    omf.classList.add("in");
}

function closeMoreFilter() {
    var cls = document.getElementById("more_filter");
    cls.classList.remove("in");
}

if (typeof $ !== 'undefined' && $('#more_filter').length > 0) {
    $(document).on('mouseup', function (e) {
        var container = $("#more_filter");
        var searchField = $(".bs-searchbox");
        var select2Field = $("#bs-select-2");
        var selectField = $(".bs-container");

        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0 && !searchField.is(e.target) && searchField.has(e.target).length === 0 && !select2Field.is(e.target) && select2Field.has(e.target).length === 0 && selectField.has(e.target).length === 0) {
            closeMoreFilter()
        }
    });
}


/*******************************************************
                Header More Filter End
*******************************************************/

/*******************************************************
                    Mobile Menu Start
*******************************************************/
function openMobileMenu() {
    var omm = document.getElementById("mobile_menu_collapse");
    omm.classList.add("toggled");

    var omm1 = document.getElementById("mobile_close_panel");
    omm1.classList.add("toggled");
}

function closeMobileMenu() {
    var cmm = document.getElementById("mobile_menu_collapse");
    cmm.classList.remove("toggled");

    var cmm1 = document.getElementById("mobile_close_panel");
    cmm1.classList.remove("toggled");
}
/*******************************************************
                    Mobile Menu End
*******************************************************/

/*******************************************************
              Mobile Admin Dashboard Open
*******************************************************/
function openAdminDashboard() {
    var oad1 = document.getElementById("mob-admin-dash");
    oad1.classList.add("in");

    var oad2 = document.getElementById("close-admin-overlay");
    oad2.classList.add("in");
}

var el = document.getElementById('close-admin-overlay');
if (el) {
    el.addEventListener("click", closeAdminDashboard);
}

var el = document.getElementById('close-admin');
if (el) {
    el.addEventListener("click", closeAdminDashboard);
}

function closeAdminDashboard() {
    var cad1 = document.getElementById("mob-admin-dash");
    cad1.classList.remove("in");

    var cad2 = document.getElementById("close-admin-overlay");
    cad2.classList.remove("in");
}
/*******************************************************
                    Mobile Settings End
*******************************************************/

/*******************************************************
                    Mobile Settings Open
*******************************************************/
function openSettingsSidebar() {
    var oss1 = document.getElementById("mob-settings-sidebar");
    oss1.classList.add("in");

    var oss2 = document.getElementById("close-settings-overlay");
    oss2.classList.add("in");
}

var el = document.getElementById('close-settings');
if (el) {
    el.addEventListener("click", closeSettingsSidebar);
}

var el = document.getElementById('close-settings-overlay');
if (el) {
    el.addEventListener("click", closeSettingsSidebar);
}

function closeSettingsSidebar() {
    var cls1 = document.getElementById("mob-settings-sidebar");
    cls1.classList.remove("in");

    var cls2 = document.getElementById("close-settings-overlay");
    cls2.classList.remove("in");
}
/*******************************************************
                    Mobile Settings End
*******************************************************/

/*******************************************************
                    Mobile Ticket Open
*******************************************************/
function openTicketsSidebar() {
    var ots1 = document.getElementById("ticket-detail-contact");
    ots1.classList.add("in");

    var oss2 = document.getElementById("close-tickets-overlay");
    oss2.classList.add("in");
}

var el = document.getElementById('close-tickets');
if (el) {
    el.addEventListener("click", closeTicketsSidebar);
}

var el = document.getElementById('close-tickets-overlay');
if (el) {
    el.addEventListener("click", closeTicketsSidebar);
}

function closeTicketsSidebar() {
    var cts1 = document.getElementById("ticket-detail-contact");
    cts1.classList.remove("in");

    var cts2 = document.getElementById("close-tickets-overlay");
    cts2.classList.remove("in");
}
/*******************************************************
                    Mobile Ticket End
*******************************************************/

/*******************************************************
                    Client Detail Open
*******************************************************/
function openClientDetailSidebar() {
    var ocds1 = document.getElementById("mob-client-detail");
    ocds1.classList.add("in");

    var ocds2 = document.getElementById("close-client-overlay");
    ocds2.classList.add("in");

    // var ocds4 = document.getElementById("close-client-detail");
    // ocds4.classList.remove("d-none");

    var ocds3 = document.getElementById("hide-project-menues");
    ocds3.classList.add("in");
}

var el = document.getElementById('close-client-overlay');
if (el) {
    el.addEventListener("click", closeClientDetail);
}

var el = document.getElementById('close-client-detail');
if (el) {
    el.addEventListener("click", closeClientDetail);
}

function closeClientDetail() {
    var ccds1 = document.getElementById("mob-client-detail");
    ccds1.classList.remove("in");

    var ccds2 = document.getElementById("close-client-overlay");
    ccds2.classList.remove("in");

    // var ccds4 = document.getElementById("close-client-detail");
    // ccds4.classList.add("d-none");

    var ccds3 = document.getElementById("hide-project-menues");
    ccds3.classList.remove("in");
}
/*******************************************************
                    Client Detail End
*******************************************************/
