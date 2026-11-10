using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using GuateGanado.Api.Models;
using Microsoft.IdentityModel.Tokens;

namespace GuateGanado.Api.Services;

public interface ITokenService
{
    (string Token, int ExpiresIn) Emitir(Usuario usuario);
}

public class TokenService : ITokenService
{
    private readonly IConfiguration _config;
    public TokenService(IConfiguration config) => _config = config;

    public (string Token, int ExpiresIn) Emitir(Usuario usuario)
    {
        var key = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(_config["Jwt:Key"]!));
        var creds = new SigningCredentials(key, SecurityAlgorithms.HmacSha256);
        var minutos = int.Parse(_config["Jwt:ExpiresMinutes"] ?? "60");

        var claims = new[]
        {
            new Claim(JwtRegisteredClaimNames.Sub, usuario.Id.ToString()),
            new Claim(JwtRegisteredClaimNames.Email, usuario.Email),
            new Claim(ClaimTypes.Role, usuario.Rol),
            new Claim(JwtRegisteredClaimNames.Jti, Guid.NewGuid().ToString())
        };

        var token = new JwtSecurityToken(
            issuer: _config["Jwt:Issuer"],
            audience: _config["Jwt:Audience"],
            claims: claims,
            expires: DateTime.UtcNow.AddMinutes(minutos),
            signingCredentials: creds);

        return (new JwtSecurityTokenHandler().WriteToken(token), minutos * 60);
    }
}
