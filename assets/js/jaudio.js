// Script info:
// - Author: Michael Mammoliti
// - Name: jAudio.js
// - Version: 0.2.1
// - js dipendencies: jQuery
// - First Release: 25 November 2015
// - Last Update: 13 November 2016
// - GitHub: https://github.com/MichaelMammoliti/jAudio.js

// Contact info
// - GitHub: https://github.com/MichaelMammoliti
// - Mail: mammoliti.michael@gmail.com
// - Twitter: @MichMammoliti

// License Info
// - Released under the MIT license.

// Mod by MrT98

(function($){


  var pluginName = "jAudio";

  function Plugin( $context, options )
  {
    this.settings           = options;
    this.$context           = $context;
    this.domAudio           = this.$context.find("audio")[0];
    this.$domPlaylist       = this.$context.find(".jAudio--playlist");
    this.$domControls       = this.$context.find(".jAudio--controls");
    this.$domVolumeBar      = this.$context.find(".jAudio--volume");
    this.$domDetails        = this.$context.find(".jAudio--details");
    this.$domStatusBar      = this.$context.find(".jAudio--status-bar");
    this.$domProgressBar    = this.$context.find(".jAudio--progress-bar-wrapper");
    this.$domWrapperClick   = this.$context.find(".jAudio--progress-wrapper-click");
    this.$domBufferedBar    = this.$context.find(".jAudio--progress-bar-loaded");
    this.$domTime           = this.$context.find(".jAudio--time");
    this.$domElapsedTime    = this.$context.find(".jAudio--time-elapsed");
    this.$domTotalTime      = this.$context.find(".jAudio--time-total");
    this.$domThumb          = this.$context.find(".jAudio--thumb");
    this.$domCover          = this.$context.find(".jAudio--cover");
    this.currentState       = "pause";
    this.currentTrack       = this.settings.defaultTrack;
    this.currentElapsedTime = undefined;
    this.timer              = undefined;

    this.init();
    
  }

  Plugin.prototype = {

    init: function()
    {
      var self = this;

      self.renderPlaylist();
      self.preLoadTrack();
      self.highlightTrack();
      self.updateTotalTime();
      self.events();
      self.debug();
      self.domAudio.volume = 1.0;
    },

    play: function($btn)
    {
      var self = this;

      self.domAudio.play();

      if(self.currentState === "play") return;

      clearInterval(self.timer);
      self.timer = setInterval( self.run.bind(self), 50 );

      self.currentState = "play";

      var tracks   = self.$domPlaylist.children(),
          classDel = "play",
          classAdd = "stop",
          infoPlay = ".jAudio--play-info";

      if(self.settings.playlist.length > 1) {
        //tracks.eq(self.currentTrack).addClass(classAdd)
        mrt = tracks.find(infoPlay).removeClass(classDel).addClass(classAdd);
      }

      // change id
      $btn.data("action", "pause");
      $btn.removeClass("jAudio--control-play");
      $btn.addClass("jAudio--control-pause");

      // activate
      $btn.toggleClass('active');
    },

    pause: function($btn)
    {
      var self        = this;

      self.domAudio.pause();
      clearInterval(self.timer);

      self.currentState = "pause";

      var tracks   = self.$domPlaylist.children(),
          classDel = "stop",
          classAdd = "play",
          infoPlay = ".jAudio--play-info";

      if(self.settings.playlist.length > 1) {
        //tracks.eq(self.currentTrack).addClass(classAdd)
        mrt = tracks.find(infoPlay).removeClass(classDel).addClass(classAdd);
      }

      // change id
      $btn.data("action", "play");
      $btn.removeClass("jAudio--control-pause");
      $btn.addClass("jAudio--control-play");

      // activate
      $btn.toggleClass('active');

    },

    stop: function($btn)
    {
      var self = this;

      self.domAudio.pause();
      self.domAudio.currentTime = 0;

      self.animateProgressBarPosition();
      clearInterval(self.timer);
      self.updateElapsedTime();

      self.currentState = "stop";
    },

    prev: function($btn)
    {
      var self  = this,
          track;

      (self.currentTrack === 0)
        ? track = self.settings.playlist.length - 1
        : track = self.currentTrack - 1;

      self.changeTrack(track);
    },

    next: function($btn)
    {
      var self = this,
          track;

      (self.currentTrack === self.settings.playlist.length - 1)
        ? track = 0
        : track = self.currentTrack + 1;

      self.changeTrack(track);
    },

    preLoadTrack: function()
    {
      var self      = this,
          defTrack  = self.settings.defaultTrack;

      self.changeTrack( defTrack );

      self.stop();
    },

    changeTrack: function(index)
    {
      var self = this;

      self.currentTrack = index;
      if (self.settings.playlist[index]["zingMp3"]) {
        $.getJSON(self.settings.playlist[index]["file"], function(data){
          self.domAudio.src = data.data['source']['128'];
          self.settings.playlist[index].file = data.data['source']['128'];
          self.settings.playlist[index].zingMp3 = 0;
          if(self.currentState === "play" || self.settings.autoPlay) self.play();
        });
      } else self.domAudio.src = self.settings.playlist[index].file;

      if(self.currentState === "play" || self.settings.autoPlay) self.play();

      if(self.settings.playlist.length > 1) {
        self.highlightTrack();
      }

      self.updateThumb();
      self.updateCover();

      self.renderDetails();
    },

    events: function()
    {
      var self = this;

      // - controls events
      self.$domControls.on("click", ".jAudio--control", function()
      {

        var $btn    = $(this),
            action  = $btn.data("action")
        ;

        switch( action )
        {
          case "prev":  self.prev.call(self,  $btn); break;
          case "next":  self.next.call(self,  $btn); break;
          case "pause": self.pause.call(self, $btn); break;
          case "stop":  self.stop.call(self,  $btn); break;
          case "play":  self.play.call(self,  $btn); break;
        };

      });

      // - playlist events
      self.$domPlaylist.on("click", ".jAudio--playlist-item", function(e)
      {
        var item = $(this),
            track = item.data("track"),
            index = item.index();

        if(self.currentTrack === index) return;

        self.changeTrack(index);
      });

      // - volume's bar events
      /**
      var Player =
      {
        volume: function(vol) { if(window.console) return console.log('Set Volume to', vol, '%'), !1 }
      };

      $('.jAudio--progress-bar-pointer').draggable(
      {
        axis: 'x',
        drag: function()
        {
          var p = $(this).position().left / $(this).parent().width();

          Player.volume(p * 100);

          if(p < 0) return $(this).css('left', 0), !1;
          else if(p > 1) return $(this).css('left', $(this).parent().width()), !1;

          $('main > .controls > .volume > em').css('width', (p * 100) + '%');
        }
      });
      */
      // to do

      // - progress bar events
      self.$domWrapperClick.on("click", function(e)
      {
        self.updateProgressBar(e);
        self.updateElapsedTime();
      });

      $(self.domAudio).on("loadedmetadata", function()
      {
        self.animateProgressBarPosition.call(self);
        self.updateElapsedTime.call(self);
        self.updateTotalTime.call(self);
      });
    },

    getAudioMiliSeconds:function(string)
    {
      var self   = this,
          string = string % 60,
          string = string - Math.floor(string),
          string = String(string).split(".")[1],
          string = String(string).substr(0,2),
          string = self.addZero( Math.floor(string), 2 );

      (string < 100) ? string = string : string = "00";

      return string;
    },

    getAudioSeconds: function(string)
    {
      var self    = this,
          string  = string % 60;
          string  = self.addZero( Math.floor(string), 2 );

      (string < 60) ? string = string : string = "00";

      return string;
    },

    getAudioMinutes: function(string)
    {
      var self    = this,
          string  = string / 60;
          string  = self.addZero( Math.floor(string), 2 );

        if (isNaN(string))
          string = "00";

      return string;
    },

    addZero: function(word, howManyZero)
    {
      var word = String(word);

      while(word.length < howManyZero) word = "0" + word;

      return word;
    },

    removeZero: function(word, howManyZero)
    {
      var word  = String(word),
          i     = 0;

      while(i < howManyZero)
      {
        if(word[0] === "0") { word = word.substr(1, word.length); } else { break; }

        i++;
      }

      return word;
    },

    highlightTrack: function()
    {
      var self      = this,
          tracks    = self.$domPlaylist.children(),
          className = "active",
          infoPlay  = ".jAudio--play-info";

      if(self.settings.playlist.length > 1) {
        tracks.removeClass(className);
        tracks.find(infoPlay).remove();
        if (self.currentState === "play")
          var addinfoPlay = '<div class="jAudio--play-info stop"></div>';
        else
          var addinfoPlay = '<div class="jAudio--play-info play"></div>';

        tracks.eq(self.currentTrack).addClass(className).prepend(addinfoPlay);
      }
    },

    renderDetails: function()
    {
      var self          = this,
          track         = self.settings.playlist[self.currentTrack],
          file          = track.file,
          thumb         = track.thumb,
          cover         = track.cover,
          trackName     = track.trackName,
          trackArtist   = track.trackArtist,
          trackAlbum    = track.trackAlbum,
          template      =  "";

          template += "<p>";
          template += "<span class='trackName'><i class='material-icons'>&#xE405;</i>" + trackName + "</span>";
          // template += " - ";
          template += "<span class='trackArtist'><i class='material-icons'>&#xE029;</i>" + trackArtist + "</span>";
          // template += "<span class='trackAlbum'>" + trackAlbum + "</span>";
          template += "</p>";


      self.$domDetails.html(template);

    },

    renderPlaylist: function()
    {
      var self = this,
          template = "";

      if(self.settings.playlist.length > 1) {
        $.each(self.settings.playlist, function(i, a)
        {

          var numberi = self.addZero(i + 1, 2),
              file          = a["file"],
              thumb         = a["thumb"],
              cover         = a["cover"],
              trackName     = a["trackName"],
              trackArtist   = a["trackArtist"],
              trackAlbum    = a["trackAlbum"];
              trackDuration = "00:00";

          //template += "<div class='jAudio--playlist-item' data-track='" + file + "'>";
          template += "<div class='jAudio--playlist-item'>";
          template += "<div class='jAudio--playlist-thumb'><img src='"+ thumb +"'></div>";
          template += "<div class='jAudio--playlist-meta'>";
          template += "<p class='jAudio--playlist-meta-track-name'>" + numberi + " - " + trackName + "</p>";
          template += "<p class='jAudio--playlist-meta-track-artist'>" + trackArtist + "</p>";
          template += "</div>";
          // template += "<div class='jAudio--playlist-track-duration'>" + trackDuration + "</div>";
          template += "</div>";

        // });

        });

        self.$domPlaylist.html(template);
      }

    },

    run: function()
    {
      var self = this;

      self.animateProgressBarPosition();
      self.updateElapsedTime();

      if(self.domAudio.ended) self.next();
    },

    animateProgressBarPosition: function()
    {
      var self              = this,
          duration          = self.domAudio.duration,
          percentage        = (self.domAudio.currentTime * 100 / duration),
          styles            = { "width": percentage + "%" };

      self.$domProgressBar.children().eq(0).css(styles);
      self.updateBufferedBar();
    },
    updateBufferedBar: function()
    {
      var self              = this,
          duration          = self.domAudio.duration,
          percentage        = (self.domAudio.currentTime * 100 / duration),
          buffered          = self.domAudio.buffered,
          wrapperWidth      = self.$domProgressBar.width(),
          updatePlayedWidth = percentage * wrapperWidth / 100,
          bufferedStyles    = {};

      if (duration > 0) {
        for (var i = 0; i < buffered.length; i++) {
          var countPx = ((buffered.end(buffered.length - 1 - i) / duration) * 100) * wrapperWidth / 100;
          countPx = countPx - updatePlayedWidth + 30;

          if (countPx < 30)
            countPx = 30;

          bufferedStyles = { "width": countPx + "px", "left": updatePlayedWidth + "px" };

          break;
        }
      }

      self.$domBufferedBar.css(bufferedStyles);
    },

    updateProgressBar: function(e)
    {
      var self = this,
          mouseX,
          percentage,
          newTime;

      if(e.offsetX) mouseX = e.offsetX;
      if(mouseX === undefined && e.layerX) mouseX = e.layerX;

      percentage  = mouseX / self.$domProgressBar.width();
      newTime     = self.domAudio.duration * percentage;

      self.domAudio.currentTime = newTime;
      self.animateProgressBarPosition();
    },

    updateElapsedTime: function()
    {
      var self        = this,
          time        = self.domAudio.currentTime,
          minutes     = self.getAudioMinutes(time),
          seconds     = self.getAudioSeconds(time),
          //miliseconds = self.getAudioMiliSeconds(time),
          //audioTime   = minutes + ":" + seconds + ":" + miliseconds;
          audioTime   = minutes + ":" + seconds;
      self.$domElapsedTime.text( audioTime );
    },

    updateTotalTime: function()
    {
      var self        = this,
          time        = self.domAudio.duration,
          minutes     = self.getAudioMinutes(time),
          seconds     = self.getAudioSeconds(time),
          audioTime   = minutes + ":" + seconds;

      self.$domTotalTime.text( audioTime );
    },

    updateThumb: function()
    {
      var self = this,
          thumb = self.settings.playlist[self.currentTrack].thumb,
          styles = {
            "background-image": "url(" + thumb + ")"
          };

      self.$domThumb.css(styles);
    },

    updateCover: function()
    {
      var self = this,
          cover = self.settings.playlist[self.currentTrack].cover,
          styles = {
            "background-image": "url(" + cover + ")"
          };

      self.$domCover.css(styles);
    },

    debug: function()
    {
      var self = this;

      if(self.settings.debug) console.log(self.settings);
    }

  }

  $.fn[pluginName] = function( options )
  {
    var instantiate = function()
    {
      return new Plugin( $(this), options );
    }

    $(this).each(instantiate);
  }

})(jQuery)
