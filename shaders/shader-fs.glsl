 precision mediump float;
uniform sampler2D sampler, samplerShadowMap;

//uniform vec3 source_direction;
varying vec2 vUV;
//varying vec3 vNormal, vLightPos;
varying vec3 vLightPos;
//varying mat4 _Nmatrix;
//varying vec3 L;
//varying float lambertTerm;
varying vec3 I_ambient;
varying vec3 I_diffuse;

//const vec3 source_ambient_color=vec3(1.0,1.0,1.0);
//const vec3 source_diffuse_color=vec3(1.0,1.0,1.0);
//const vec3 mat_ambient_color=vec3(0.3,0.3,0.3);
//const vec3 mat_diffuse_color=vec3(1.0,1.0,1.0);
//const float mat_shininess=10.0;

void main(void) {
vec2 uv_shadowMap=vLightPos.xy;
vec4 shadowMapColor=texture2D(samplerShadowMap, uv_shadowMap);
float zShadowMap=shadowMapColor.r;
float shadowCoeff=1.0-smoothstep(0.002, 0.003, vLightPos.z-zShadowMap);
vec3 color=vec3(texture2D(sampler, vUV));

//vec3 L = normalize(source_direction); // Перенести в вершинный шейдер

//vec3 s_direction = vec3(_Nmatrix * vec4(L,0.0));

//vec3 I_ambient=source_ambient_color*mat_ambient_color;
//vec3 I_diffuse=source_diffuse_color*mat_diffuse_color*lambertTerm;
//vec3 I_diffuse=source_diffuse_color*mat_diffuse_color*clamp(dot(vNormal, L), 0.0, 1.0);

vec3 I=I_ambient+shadowCoeff*I_diffuse;
gl_FragColor = vec4(I*color, 1.0);
}