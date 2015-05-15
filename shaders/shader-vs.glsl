attribute vec3 position, normal;
attribute vec2 uv;
uniform mat4 Pmatrix, Mmatrix, MMmatrix, Lmatrix, Nmatrix, PmatrixLight;
uniform vec3 source_direction;

uniform vec3 source_ambient_color;
uniform vec3 source_diffuse_color;
uniform vec3 mat_ambient_color;
uniform vec3 mat_diffuse_color;

varying vec2 vUV;
//varying vec3 vNormal,
varying vec3 vLightPos;
//varying mat4 _Nmatrix;

//varying vec3 L;

//varying float lambertTerm;
varying vec3 I_ambient;
varying vec3 I_diffuse;


void main(void) {

//Shadow mapping : \n\
vec4 lightPos = PmatrixLight*Lmatrix*MMmatrix*vec4(position, 1.0);
//lightPos=PmatrixLight*lightPos;
vec3 lightPosDNC=lightPos.xyz/lightPos.w;
vLightPos=vec3(0.5,0.5,0.5)+lightPosDNC*0.5;

gl_Position = Pmatrix*Mmatrix*MMmatrix*vec4(position, 1.);

 //vNormal= normal;

 vec4 normals = Nmatrix * vec4(normal, 1.0);
 vec3 vNormal = normalize(normals.xyz);

 vec3 L = normalize(source_direction);

 float lambertTerm = clamp(dot(vNormal, L), 0.0, 1.0);

 I_ambient=source_ambient_color*mat_ambient_color;
 I_diffuse=source_diffuse_color*mat_diffuse_color*lambertTerm;

 //_Nmatrix = Nmatrix;

vUV=uv;
}