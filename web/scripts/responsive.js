function updateLayout() {
    var leftPanel;
    var rightPanel;
    var rowPanel;
    var donateBtn;
    var closeBtn;

    if (window.innerHeight / window.innerWidth < 1) {
        $("body").css("zoom", "1");
        $("body").css("-webkit-text-size-adjust", "100%");
        $("body").css("-moz-text-size-adjust", "100%");
        $("body").css("-ms-text-size-adjust", "100%");

        var inputs = document.getElementsByTagName("input");
        for (index = 0; index < inputs.length; ++index) {
            inputs[index].style.marginBottom = "2vw";
        }

        var selects = document.getElementsByTagName("select");
        for (index = 0; index < selects.length; ++index) {
            selects[index].style.marginBottom = "2vw";
        }

        document.getElementsByTagName("textarea")[0].style.marginBottom = "2vw";

        leftPanel = document.getElementById('left-panel');
        rightPanel = document.getElementById('right-panel');
        rowPanel = document.getElementsByClassName('row')[0];
        donateBtn = document.getElementById('donate-button');
        closeBtn = document.getElementById('close-button');

        leftPanel.style.height = "100%";
        leftPanel.style.width = "66.66%";

        rightPanel.style.height = "100%";
        rightPanel.style.width = "33.33%";
        rightPanel.style.boxShadow = "-3px 4px 14px 0px #888888";

        rowPanel.style.display = "-webkit-flex";

        leftPanel.setAttribute('class', 'visible');
        rightPanel.setAttribute('class', 'visible');
        donateBtn.setAttribute('class', 'hidden');
        closeBtn.setAttribute('class', 'hidden');
    } else {
        $("body").css("zoom", "2");
        $("body").css("-webkit-text-size-adjust", "200%");
        $("body").css("-moz-text-size-adjust", "200%");
        $("body").css("-ms-text-size-adjust", "200%");

        var inputs = document.getElementsByTagName("input");
        for (index = 0; index < inputs.length; ++index) {
            inputs[index].style.marginBottom = "0vw";
        }

        var selects = document.getElementsByTagName("select");
        for (index = 0; index < selects.length; ++index) {
            selects[index].style.marginBottom = "0vw";
        }

        document.getElementsByTagName("textarea")[0].style.marginBottom = "0vw";

        leftPanel = document.getElementById('left-panel');
        rightPanel = document.getElementById('right-panel');
        donateBtn = document.getElementById('donate-button');
        closeBtn = document.getElementById('close-button');

        leftPanel.style.height = "100%";
        leftPanel.style.width = "100%";

        rightPanel.style.height = "100%";
        rightPanel.style.width = "100%";

        rightPanel.setAttribute('class', 'hidden');
        donateBtn.setAttribute('class', 'visible');
        closeBtn.setAttribute('class', 'visible');

        donateBtn.onclick = function() {
            leftPanel.setAttribute('class', 'hidden');
            rightPanel.setAttribute('class', 'visible');
        };

        closeBtn.onclick = function() {
            leftPanel.setAttribute('class', 'visible');
            rightPanel.setAttribute('class', 'hidden');
        };
    }
}

$(document).ready(updateLayout);
$(window).resize(updateLayout);