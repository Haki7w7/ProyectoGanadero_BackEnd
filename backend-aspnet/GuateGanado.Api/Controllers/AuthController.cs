using GuateGanado.Api.Data;
using GuateGanado.Api.Dtos;
using GuateGanado.Api.Services;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace GuateGanado.Api.Controllers;

[ApiController]
[Route("api/v1/auth")]
public class AuthController : ControllerBase
{
    private readonly AppDbContext _db;
    private readonly ITokenService _tokens;

    public AuthController(AppDbContext db, ITokenService tokens)
    {
        _db = db;
        _tokens = tokens;
    }

    /// <summary>POST /api/v1/auth/login -> 200 con token; 422 datos invalidos; 401 credenciales incorrectas.</summary>
    [HttpPost("login")]
    [ProducesResponseType(typeof(LoginResponseDto), StatusCodes.Status200OK)]
    [ProducesResponseType(StatusCodes.Status401Unauthorized)]
    [ProducesResponseType(StatusCodes.Status422UnprocessableEntity)]
    public async Task<IActionResult> Login([FromBody] LoginRequestDto dto)
    {
        var usuario = await _db.Usuarios.FirstOrDefaultAsync(u => u.Email == dto.Email);

        if (usuario is null || !PasswordHasher.Verify(dto.Password, usuario.PasswordHash))
            return Unauthorized(new { message = "Credenciales incorrectas." });

        var (token, expiresIn) = _tokens.Emitir(usuario);

        return Ok(new LoginResponseDto
        {
            Token = token,
            ExpiresIn = expiresIn,
            Rol = usuario.Rol
        });
    }
}
