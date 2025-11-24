(function() {function o(r,...t){return r.map((e,i)=>e+(t[i]?t[i]:"")).join("")}const n=":host{position:fixed;bottom:1rem;left:1rem;transition:all 1s ease;z-index:999}:host(:not([loaded])){opacity:0;pointer-events:none}.trigger{width:fit-content;padding:10px 20px;border-radius:31px;font-size:14px;font-weight:light;color:#000;display:flex;gap:.5rem;align-items:center;background-color:var(--qrcg-qrcode-widget-background-color, #5559df);color:var(--qrcg-qrcode-widget-text-color, white);box-shadow:#0003 0 4px 8px;cursor:pointer;-webkit-user-select:none;user-select:none}.trigger:hover{background-color:var(--qrcg-qrcode-widget-hover-background-color, rgb(75, 78, 173));color:var(--qrcg-qrcode-widget-hover-text-color, white)}.icon{width:15px;height:15px;pointer-events:none}.icon path{fill:currentColor}";class s extends HTMLElement{static tag="qrcg-int-qrcode-widget";static widgetId=null;static observedAttributes=["widget-id"];static baseUrl=null;widgetData={};static register(){customElements.define(this.tag,this)}static injectIntoDocument(){document.body.appendChild(new this)}static saveDocumentSourceDetails(){const t=document.currentScript?.src;try{const e=new URL(t);this.widgetId=e.searchParams.get("id"),this.baseUrl=e.protocol+"//"+e.host}catch{return null}}constructor(){super(),this.attachShadow({mode:"open"}),this.shadowRoot.innerHTML=`
            <style>
                ${this.styles()}
            </style>
            ${this.render()}
        `}connectedCallback(){this.addEventListener("click",this.onClick),this.fetchWidgetDetails()}disconnectedCallback(){this.removeEventListener("click",this.onClick)}attributeChangedCallback(t){t==="widget-id"&&this.onWidgetIdChange()}onWidgetIdChange(){this.fetchWidgetDetails()}async fetchWidgetDetails(){if(this.isPreview()){this.setAttribute("loaded","true");return}if(!this.getWidgetId())return;const t=`${this.getBaseUrl()}/api/widgets/integration/${this.getWidgetId()}`,i=await(await fetch(t)).json();this.setWidgetData(i),this.setAttribute("loaded","true")}getWidgetId(){return this.isPreview()?this.getAttribute("widget-id"):s.widgetId}getBaseUrl(){return this.isPreview()?window.location.protocol+"//"+window.location.host:s.baseUrl}isPreview(){return this.hasAttribute("is-preview")}onClick(t){if(this.isPreview()||!t.composedPath()[0].closest(".trigger"))return;const i=document.createElement("qrcg-int-qrcode-widget-frame");i.setAttribute("src",this.getDestinationUrl()),this.syncWidgetPosition(i),document.body.appendChild(i)}styles(){return n}setWidgetData(t){this.widgetData=t,this.syncWidgetData()}syncWidgetData(){this.syncWidgetText(),this.syncWidgetIcon(),this.syncWidgetPosition(),this.syncWidgetColor("widget-background-color"),this.syncWidgetColor("widget-text-color"),this.syncWidgetColor("widget-hover-background-color"),this.syncWidgetColor("widget-hover-text-color")}syncWidgetPosition(t=this){switch(this.widgetData.widget_position){case"bottom-left":break;case"bottom-right":t.style.setProperty("left","unset"),t.style.setProperty("right","15px");break}}getDestinationUrl(){return this.widgetData.destination_url}syncWidgetIcon(){const t=this.widgetData.icon_url;if(!t)return;this.shadowRoot.querySelector(".icon").remove();const e=document.createElement("img");e.setAttribute("src",t),e.classList.add("icon"),this.shadowRoot.querySelector(".trigger").insertBefore(e,this.shadowRoot.querySelector(".text"))}syncWidgetText(){const t=this.shadowRoot.querySelector(".text"),e=this.widgetData.name;!t||!e||(t.innerHTML=e)}syncWidgetColor(t){const e=`--qrcg-qrcode-${t}`,i=t.replace(/-/g,"_");this.widgetData[i]&&this.style.setProperty(e,this.widgetData[i])}render(){return o`
            <div class="trigger">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    class="icon"
                >
                    <title>send-variant</title>
                    <path d="M3 20V14L11 12L3 10V4L22 12Z" />
                </svg>

                <div class="text">Email Me</div>
            </div>
        `}}s.register();s.saveDocumentSourceDetails();s.injectIntoDocument();const a=":host{position:fixed;bottom:15px;height:80vh;width:450px;left:15px;background-color:#fff;border-radius:18px;box-shadow:#0000001f 0 12px 48px 4px;display:flex;flex-direction:column;z-index:1000}header{display:flex;align-items:center;justify-content:space-between;padding:19px}header img{height:17px;width:auto}.close{padding:5px;border-radius:10px;display:flex;cursor:pointer}.close svg{width:27px;height:27px}.close:hover{background-color:#eee}.iframe-container{flex:1;position:relative}iframe{border:0;width:100%;height:100%}.spinner{width:20px;height:20px;border-radius:50%;background:conic-gradient(#0000 10%,#000);-webkit-mask:radial-gradient(farthest-side,rgba(0,0,0,0) calc(100% - 2.3px),#000 0);mask:radial-gradient(farthest-side,rgba(0,0,0,0) calc(100% - 2.3px),#000 0);animation:spinner-zp9dbg 1.2s infinite linear}@keyframes spinner-zp9dbg{to{transform:rotate(1turn)}}.spinner-container{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;padding:20px;z-index:1;background-color:#fff}";class c extends s{static tag="qrcg-int-qrcode-widget-frame";connectedCallback(){super.connectedCallback(),this.syncIFrameSource(),setTimeout(()=>{this.shadowRoot.querySelector(".spinner-container").remove()},1500)}fetchWidgetDetails(){}syncIFrameSource(){this.shadowRoot.querySelector("iframe").setAttribute("src",this.getAttribute("src"))}onClick(t){t.composedPath()[0].closest(".close")&&this.remove()}styles(){return a}render(){return o`
            <header>
                <img
                    class="logo"
                    src="https://quickcode.digital/quickcode/assets/images/logo.png"
                />
                <div class="close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <title>window-close</title>
                        <path
                            d="M13.46,12L19,17.54V19H17.54L12,13.46L6.46,19H5V17.54L10.54,12L5,6.46V5H6.46L12,10.54L17.54,5H19V6.46L13.46,12Z"
                        />
                    </svg>
                </div>
            </header>

            <div class="iframe-container">
                <div class="spinner-container">
                    <div class="spinner"></div>
                </div>

                <iframe
                    src="${this.getAttribute("src")}"
                    sandbox="allow-downloads allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts allow-top-navigation allow-forms allow-modals"
                >
                </iframe>
            </div>
        `}}c.register();
})()