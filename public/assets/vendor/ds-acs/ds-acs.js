function initAscMenu() {

   function generateHtml() {
      const div1 = document.createElement("div");
      div1.className = "ds_acs_menu__container";
      div1.id = "ds_acs_menu";

      const div2 = document.createElement("div");
      div2.style.display = "relative";

      const div3 = document.createElement("div");
      div3.className = "ds_acs_menu";

      const div4 = document.createElement("div");
      div4.className = "ds_acs_menu__icon";
      const img = document.createElement("img");
      img.src = "wai2.svg";
      div4.appendChild(img);

      const div5 = document.createElement("div");
      div5.className = "ds_acs_menu__button";
      div5.textContent = "+";

      const div6 = document.createElement("div");
      div6.className = "ds_acs_menu__content";
      const h4 = document.createElement("h4");
      h4.className = "ds_acs_menu__title";
      h4.textContent = "Accessibility Menu";
      const ul = document.createElement("ul");
      ul.className = "ds_acs_menu__content__list";

      const items = [{
            icon: "fas fa-search-plus",
            text: " Povećaj tekst",
            onclick: "window.ascMenu.actions('increaseFontSize')"
         },
         {
            icon: "fas fa-search-minus",
            text: " Smanji tekst",
            onclick: "window.ascMenu.actions('decreaseFontSize')"
         },
         {
            icon: "fas fa-chess-board",
            text: " Sive nijanse",
            onclick: "window.ascMenu.actions('grayscale')"
         },
         {
            icon: "fas fa-adjust",
            text: " Visoki kontrast",
            onclick: "window.ascMenu.actions('contrast')"
         },
         {
            icon: "far fa-eye",
            text: " Negativni kontrast",
            onclick: "window.ascMenu.actions('negative')"
         },
         {
            icon: "far fa-lightbulb",
            text: " Svijetla pozadina",
            onclick: "window.ascMenu.actions('light')"
         },
         {
            icon: "fas fa-font",
            text: " Čitljiva slova",
            onclick: "window.ascMenu.actions('font')"
         },
         {
            icon: "",
            text: ""
         },
         {
            icon: "fas fa-redo-alt",
            text: " Resetiraj",
            onclick: "window.ascMenu.actions('reset')"
         },
      ];

      items.forEach((item) => {
         const li = document.createElement("li");
         li.setAttribute("onclick", item.onclick);
         const i = document.createElement("i");
         i.className = item.icon;
         li.appendChild(i);
         li.appendChild(document.createTextNode(item.text));
         ul.appendChild(li);
      });

      div6.appendChild(h4);
      div6.appendChild(ul);

      div3.appendChild(div4);
      div3.appendChild(div5);
      div3.appendChild(div6);

      div2.appendChild(div3);
      div1.appendChild(div2);

      document.body.insertAdjacentHTML('afterbegin', div1.outerHTML);
   }


   var t = {};

   generateHtml();

   t._menu = document.querySelector(".ds_acs_menu");
   t._button = document.querySelector(".ds_acs_menu__button");
   t._icon = document.querySelector(".ds_acs_menu__icon");

   t.rebuildUi = function () {
      t.toggle();
      const menuElement = document.getElementById('ds_acs_menu');
      const parentElement = menuElement.parentNode;
      parentElement.removeChild(menuElement);
      generateHtml();
      t._menu = document.querySelector(".ds_acs_menu");
      t._button = document.querySelector(".ds_acs_menu__button");
      t._icon = document.querySelector(".ds_acs_menu__icon");
      t._menu.addEventListener("click", function () {
         t.toggle();
      });
   }

   t.toggle = function () {
      t._menu.classList.toggle("open");

      if (t._menu.classList.contains("open")) {
         t._icon.style.display = 'none';
         t._button.style.display = 'block';
         t._menu.classList.add("ds_acs_menu_padding");
      } else {
         t._icon.style.display = 'block';
         t._button.style.display = 'none';
         t._menu.classList.remove("ds_acs_menu_padding");
      }

   }

   t._menu.addEventListener("click", function () {
      t.toggle();
   });

   t.actions = function (action) {

      if (action === 'reset') {
         location.reload();
      } else if (action === 'decreaseFontSize') {
         const elements = document.querySelectorAll('body :not(#ds_acs_menu):not(#ds_acs_menu *)');
         for (let i = 0; i < elements.length; i++) {
            const fontSize = window.getComputedStyle(elements[i]).fontSize;
            const currentSize = parseFloat(fontSize);
            const newSize = currentSize / 1.1;
            elements[i].style.fontSize = `${newSize}px`;
         }
      } else if (action === 'increaseFontSize') {
         const elements = document.querySelectorAll('body :not(#ds_acs_menu):not(#ds_acs_menu *)');
         for (let i = 0; i < elements.length; i++) {
            const fontSize = window.getComputedStyle(elements[i]).fontSize;
            const currentSize = parseFloat(fontSize);
            const newSize = currentSize * 1.1;
            elements[i].style.fontSize = `${newSize}px`;
         }
      } else if (action === 'grayscale') {
         const elements = document.querySelectorAll('body :not(#ds_acs_menu):not(#ds_acs_menu *)');
         for (let i = 0; i < elements.length; i++) {
            elements[i].style.filter = 'grayscale(100%)';
         }
      } else if (action === 'contrast') {
         const elements = document.querySelectorAll('body :not(#ds_acs_menu):not(#ds_acs_menu *)');

         for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            element.style.filter = "contrast(200%)";
            element.style.backgroundColor = "black";
            element.style.color = "white";
         }

      } else if (action === 'negative') {
         const elements = document.querySelectorAll('body :not(#ds_acs_menu):not(#ds_acs_menu *)');

         for (let i = 0; i < elements.length; i++) {
            const element = elements[i];
            element.style.filter = "invert(100%)";
            element.style.backgroundColor = "white";
            element.style.color = "black";
         }


      } else if (action === 'font') {
         const elements = document.querySelectorAll('body :not(#ds_acs_menu):not(#ds_acs_menu *)');
         for (let i = 0; i < elements.length; i++) {
            elements[i].style.fontFamily = '"Times New Roman", Times, serif';
         }
      } else if (action === 'light') {
         const elements = document.querySelectorAll('body :not(#ds_acs_menu):not(#ds_acs_menu *)');
         for (var i = 0; i < elements.length; i++) {
            elements[i].style.setProperty("color", "black", "important");
            elements[i].style.setProperty("background-color", "white", "important");
         }
      }
   }


   return t;
};
