    precision mediump float;

    varying vec2 vTextureCoord;

    varying vec4  vFinalColor;

    uniform sampler2D uSampler;

    void main(void) {
         gl_FragColor = vFinalColor * texture2D(uSampler, vTextureCoord);
    }