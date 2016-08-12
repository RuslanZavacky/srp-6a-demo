var random16byteHex = (function () {
  function random() {
    var wordCount = 4;
    var randomWords;

    // First we're going to try to use a built-in CSPRNG
    if (window.crypto && window.crypto.getRandomValues) {
      randomWords = new Int32Array(wordCount);
      window.crypto.getRandomValues(randomWords);
    }
    // Because of course IE calls it msCrypto instead of being standard
    else if (window.msCrypto && window.msCrypto.getRandomValues) {
      randomWords = new Int32Array(wordCount);
      window.msCrypto.getRandomValues(randomWords);
    }
    // Last resort - we'll use isaac.js to get a random number. It's seeded from Math.random(),
    // but we can run it for 100ms/0.1s to advance it a distance which will be dependent upon
    // hardware and js engine. Also we will have the onkeyup skip a char worth of values.
    else {
      randomWords = [];
      for (var i = 0; i < wordCount; i++) {
        randomWords.push(isaac.rand());
      }
    }

    var string = '';

    for (var i = 0; i < wordCount; i++) {
      var int32 = randomWords[i];
      if (int32 < 0) int32 = -1 * int32;
      string = string + int32.toString(16);
    }

    return string;
  }
  
  function isCrypto() {
    if (window.crypto && window.crypto.getRandomValues) {
      return true;
    }

    return !!(window.msCrypto && window.msCrypto.getRandomValues);
  }

  var crypto = isCrypto();

  function advance(ms) {
    if (!crypto) {
      var start = Date.now();
      var end = start + ms;
      while (Date.now() < end) {
        var r = isaac.random() * 128 + (Date.now() - start);
        isaac.prng(Math.floor(r));
      }
    }
  }

  return {
    'random': random,
    'isCrypto': crypto,
    'advance': advance
  };
})();

// if it is isaac spend 0.1s advancing the stream
random16byteHex.advance(100);

