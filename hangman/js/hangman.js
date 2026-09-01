/* global Config, lang */
(function () {
    "use strict";

    // FusionCMS places module JavaScript in the document head. Wait until the
    // template has rendered the game container instead of silently stopping.
    function init(){
    var root=document.getElementById("hangman");
    if(!root)return;

    var baseUrl=root.getAttribute("data-url");
    var state=JSON.parse(root.getAttribute("data-state")||"{}");
    var busy=false;

    function t(key,fallback){
        try{
            var v=lang(key,"hangman");
            if(v&&v!==key)return v;
        }catch(e){}
        return fallback;
    }

    function esc(v){
        return String(v).replace(/[&<>"']/g,function(c){
            return {"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c];
        });
    }

    function rewardText(){
        var template=t("rewardPreview","Win reward: %s Vote Points + %s Donation Points");
        return template.replace("%s",state.rewardPreviewVp).replace("%s",state.rewardPreviewDp);
    }

    function send(payload){
        if(busy)return;
        busy=true;
        root.classList.add("is-busy");

        var body=new FormData();
        Object.keys(payload).forEach(function(k){body.append(k,payload[k]);});
        if(typeof Config!=="undefined"&&Config.CSRF)body.append(Config.CSRF.name,Config.CSRF.hash);

        fetch(baseUrl+"hangman/play",{
            method:"POST",body:body,credentials:"same-origin",
            headers:{"X-Requested-With":"XMLHttpRequest"}
        }).then(function(r){return r.json();})
        .then(function(data){state=data;render();})
        .catch(function(){})
        .then(function(){busy=false;root.classList.remove("is-busy");});
    }

    function renderMask(){
        return state.mask.map(function(item){
            if(!item.isLetter)return '<span class="hangman-char hangman-char-space">'+esc(item.char)+'</span>';
            return '<span class="hangman-char'+(item.revealed?" is-revealed":"")+'">'+
                (item.revealed?esc(item.char):"&nbsp;")+"</span>";
        }).join("");
    }

    function renderKeyboard(){
        if(!state.letterButtons||!state.hasGame||state.won||state.over)return "";
        return '<div class="hangman-keyboard">'+state.alphabet.map(function(letter){
            var used=state.letters.indexOf(letter)!==-1;
            return '<button type="button" class="hangman-key" data-letter="'+esc(letter)+'"'+
                (used?" disabled":"")+">"+esc(letter.toUpperCase())+"</button>";
        }).join("")+"</div>";
    }

    function renderInput(){
        if(state.letterButtons||!state.hasGame||state.won||state.over)return "";
        return '<form class="hangman-input-form"><input type="text" maxlength="1" class="hangman-input" '+
            'aria-label="'+esc(t("letter","Letter"))+'"><button type="submit" class="hangman-btn">'+
            esc(t("guess","Guess"))+"</button></form>";
    }

    function renderDifficulty(){
        return '<div class="hangman-difficulty"><label for="hangman-difficulty-select">'+
            esc(t("difficulty","Difficulty"))+'</label> <select id="hangman-difficulty-select" class="hangman-select">'+
            state.difficulties.map(function(d){
                return '<option value="'+d.id+'"'+(d.id===state.difficulty?" selected":"")+'>'+esc(d.name)+'</option>';
            }).join("")+"</select></div>";
    }

    function render(){
        if(state.loginRequired){
            root.innerHTML='<div class="alert alert-warning">'+esc(t("loginfirst","Please log in or enable guest play."))+"</div>";
            return;
        }

        var html=renderDifficulty();

        if(!state.hasGame){
            if(state.rewardEnabled){
                html+='<div class="hangman-reward-preview">'+esc(rewardText())+"</div>";
            }
            if(state.isGuest){
                html+='<div class="alert alert-info">'+esc(t("guestRewardNotice","Guests can play, but rewards are only a preview."))+"</div>";
            }
            html+='<button type="button" class="hangman-btn" data-action="new">'+
                esc(t("newGame","New game"))+"</button>";
            root.innerHTML=html;
            return;
        }

        var percent=Math.max(0,Math.min(100,(state.health/state.maxHealth)*100));
        html+='<div class="hangman-board">';
        html+='<div class="hangman-image"><img src="'+esc(baseUrl+"hangman/img/"+state.wrong)+'" alt="Hangman"></div>';
        html+='<div class="hangman-panel">';
        html+='<div class="hangman-word">'+renderMask()+"</div>";
        html+='<div class="hangman-health"><div class="hangman-health-bar" style="width:'+percent+'%"></div></div>';
        html+='<div class="hangman-meta"><strong>'+esc(t("score","Score"))+
            ":</strong> "+state.score+" &nbsp; <strong>"+esc(t("wrongGuesses","Wrong guesses"))+
            ":</strong> "+state.wrong+"</div>";

        if(state.rewardEnabled&&!state.won&&!state.over){
            html+='<div class="hangman-reward-preview">'+esc(rewardText())+"</div>";
        }

        if(state.isGuest){
            html+='<div class="alert alert-info">'+esc(t("guestRewardNotice","Guests can play, but rewards are only a preview."))+"</div>";
        }

        if(state.won){
            html+='<div class="alert alert-success">'+esc(t("gameWinMsg","Congratulations, you won!"))+"</div>";
            if(state.isGuest){
                if(state.rewardEnabled)html+='<div class="alert alert-info">'+esc(rewardText())+"</div>";
            }else if(state.rewarded){
                var received=t("rewardReceived","Reward received: %s Vote Points + %s Donation Points");
                html+='<div class="alert alert-success">'+esc(received.replace("%s",state.rewardVp).replace("%s",state.rewardDp))+"</div>";
            }
        }else if(state.over){
            html+='<div class="alert alert-danger">'+esc(t("gameLosMsg","Game over, you lost!"))+"</div>";
        }else{
            html+=renderKeyboard()+renderInput();
        }

        if(state.won||state.over){
            html+='<button type="button" class="hangman-btn" data-action="new">'+
                esc(t("playAgain","Play again"))+"</button>";
        }

        html+="</div></div>";
        root.innerHTML=html;
    }

    root.addEventListener("click",function(e){
        var key=e.target.closest("[data-letter]");
        if(key){send({action:"guess",letter:key.getAttribute("data-letter")});return;}
        var action=e.target.closest("[data-action]");
        if(action&&action.getAttribute("data-action")==="new"){
            var select=root.querySelector("#hangman-difficulty-select");
            send({action:"new",difficulty:select?select.value:state.difficulty});
        }
    });

    root.addEventListener("change",function(e){
        if(e.target.id==="hangman-difficulty-select"){
            send({action:"difficulty",difficulty:e.target.value});
        }
    });

    root.addEventListener("submit",function(e){
        if(!e.target.classList.contains("hangman-input-form"))return;
        e.preventDefault();
        var input=e.target.querySelector(".hangman-input");
        if(input&&input.value){
            send({action:"guess",letter:input.value.charAt(0)});
            input.value="";
        }
    });

    render();
    }

    if(document.readyState === "loading"){
        document.addEventListener("DOMContentLoaded",init);
    }else{
        init();
    }
})();
