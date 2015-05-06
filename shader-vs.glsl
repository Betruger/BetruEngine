     attribute vec3 aVertexPosition;
    attribute vec2 aTextureCoord;
    attribute vec3 aVertexNormal;

    uniform mat4 uMVMatrix;
    uniform mat4 uPMatrix;
    uniform mat4 uNMatrix;

    uniform vec3 uLightPosition;
    uniform vec4 uLightAmbient;
    uniform vec4 uLightDiffuse;

    uniform vec4 uMaterialDiffuse;

    varying vec4 vFinalColor;

    varying vec2 vTextureCoord;

    void main(void) {

     vec3 L = normalize(-uLightPosition);

     vec4 normal = uNMatrix * vec4(aVertexNormal, 1.0);

     vec3 N = normalize(normal.xyz);

    L = vec3(uNMatrix * vec4(L,0.0));

	    float lambertTerm = clamp(dot(N,-L), 0.0, 1.0);

	     vec4 Ia = uLightAmbient;
        vec4 Id = uMaterialDiffuse * uLightDiffuse * lambertTerm;

        vFinalColor = Ia + Id;
        vFinalColor.a = 1.0;

        gl_Position = uPMatrix * uMVMatrix * vec4(aVertexPosition, 1.0);
        vTextureCoord = aTextureCoord;
    }